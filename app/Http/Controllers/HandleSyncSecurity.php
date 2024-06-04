<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Security;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Throwable;

class HandleSyncSecurity extends Controller
{
    public function processInBatch(Request $request)
    {
        try {
            $requests = $request->validate([
                'security_type_id' => 'required|integer',
            ]);
            $securities = Security::where('security_type_id', $requests['security_type_id'])->get();

            if ($securities->isNotEmpty()) {
                $batch = Bus::batch([])->dispatch();

                foreach ($securities as $security) {
                    $batch->add(new ProcessMicroserviceABC(['symbol' => $security->symbol, 'security_type_id' => $requests['security_type_id']]));
                }

                $batch->then(function () {
                    Mail::raw('Prices successfully synced.', function ($message) {
                        $message->to('admin@example.com')->subject('Sync Complete');
                    });
                })->dispatch();

                return response()->json(['message' => 'Batch processing initiated.'], 200);
            }

            return response()->json(['message' => 'No securities found.'], 404);

        } catch (Throwable $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
