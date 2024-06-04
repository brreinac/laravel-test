<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Security;
use App\Models\SecurityType;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class HandleSyncSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_in_batch_success()
    {
        // Crear un SecurityType y Securities de prueba
        $securityType = SecurityType::factory()->create(['slug' => 'mutual_funds']);
        $security1 = Security::factory()->create(['security_type_id' => $securityType->id, 'symbol' => 'APPL']);
        $security2 = Security::factory()->create(['security_type_id' => $securityType->id, 'symbol' => 'TSLA']);

        // Mocking the Bus facade
        Bus::fake();

        // Mocking the Mail facade
        Mail::fake();

        $response = $this->postJson('/api/sync-in-batch', [
            'security_type_id' => $securityType->id
        ]);

        // Asegúrate de que la respuesta sea exitosa
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Batch processing initiated.']);

        // Verificar que los trabajos en lote fueron despachados
        Bus::assertBatchCount(1);

        // Verificar que el correo electrónico se envió
        Mail::assertSent(function ($mail) {
            return $mail->to[0]['address'] === 'admin@example.com';
        });
    }

    public function test_process_in_batch_no_securities_found()
    {
        // Crear un SecurityType sin Securities
        $securityType = SecurityType::factory()->create(['slug' => 'mutual_funds']);

        $response = $this->postJson('/api/sync-in-batch', [
            'security_type_id' => $securityType->id
        ]);

        // Asegúrate de que la respuesta sea de no encontrados
        $response->assertStatus(404);
        $response->assertJson(['message' => 'No securities found.']);
    }
}
