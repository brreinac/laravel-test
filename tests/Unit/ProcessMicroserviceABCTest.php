<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Security;
use App\Models\SecurityType;
use App\Models\SecurityPrice;
use App\Jobs\ProcessMicroserviceABC;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessMicroserviceABCTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_job_success()
    {
        // Crear un Security de prueba
        $securityType = SecurityType::factory()->create(['slug' => 'mutual_funds']);
        $security = Security::factory()->create(['security_type_id' => $securityType->id, 'symbol' => 'APPL']);

        // Instancia del trabajo con datos de prueba
        $job = new ProcessMicroserviceABC(['symbol' => 'APPL', 'security_type_id' => $securityType->id]);

        // Ejecutar el trabajo
        $job->handle();

        // Verificar que el precio fue actualizado en la base de datos
        $this->assertDatabaseHas('security_prices', [
            'security_id' => $security->id,
            'last_price' => 188.97,
            'as_of_date' => '2023-10-30 17:31:18'
        ]);
    }

    public function test_handle_job_symbol_not_found()
    {
        // Crear un Security de prueba con un símbolo diferente
        $securityType = SecurityType::factory()->create(['slug' => 'mutual_funds']);
        $security = Security::factory()->create(['security_type_id' => $securityType->id, 'symbol' => 'GOOG']);

        // Instancia del trabajo con datos de prueba
        $job = new ProcessMicroserviceABC(['symbol' => 'APPL', 'security_type_id' => $securityType->id]);

        // Ejecutar el trabajo
        $job->handle();

        // Verificar que el precio no fue actualizado en la base de datos
        $this->assertDatabaseMissing('security_prices', [
            'security_id' => $security->id,
            'last_price' => 188.97,
            'as_of_date' => '2023-10-30 17:31:18'
        ]);
    }
}
