<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Jobs\Cloudpayments\PayFailNotification;
use App\Models\PitechNotification;
use Illuminate\Http\Request;

class pitechController extends Controller
{
    public function paySuccess(Request $request)
    {
        try {
            $data[] = $request->all();
            //PayFailNotification::dispatch($data)->onQueue('pitech_pay');

            PitechNotification::create([
                'data' => json_encode($data)
            ]);

            return response()->json([
                'code' => 0,
            ], 200);
        } catch (\Throwable $e) {
           // \Log::info($data);
           // \Log::error($e);
            return response()->json([
                'code' => 500
            ], 500);
        }
    }
}
