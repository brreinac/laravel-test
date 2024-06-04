<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Security;
use App\Models\SecurityPrice;

class ProcessMicroserviceABC implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @param array $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Emulating the response from the JSON data
        $response = json_decode('{
            "results": [
                {
                    "symbol": "APPL",
                    "price": 188.97,
                    "last_price_datetime": "2023-10-30T17:31:18-04:00"
                },
                {
                    "symbol": "TSLA",
                    "price": 244.42,
                    "last_price_datetime": "2023-10-30T17:32:11-04:00"
                }
            ]
        }', true);

        foreach ($response['results'] as $priceData) {
            if ($priceData['symbol'] === $this->data['symbol']) {
                $security = Security::where('symbol', $priceData['symbol'])->where('security_type_id', $this->data['security_type_id'])->first();

                if ($security) {
                    $security->prices()->create([
                        'last_price' => $priceData['price'],
                        'as_of_date' => $priceData['last_price_datetime']
                    ]);
                }
            }
        }
    }
}
