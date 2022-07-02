<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Jobs\Cloudpayments\PayFailNotification;
use App\Models\Card;
use App\Models\PitechNotification;
use App\Models\Subscription;
use App\Models\UserLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function saveCardSuccess(Request $request)
    {
        try {
            $data[] = $request->all();
           // dd($data[0][0]);
            if($data[0][0]['ordersState'] == 'SUCCESS') {
                Card::updateOrCreate([
                    'customer_id' => $data[0][0]['extClientRef'],
                    'token' => $data[0][0]['cardsId'],
                ],
                    [
                        'customer_id' => $data[0][0]['extClientRef'],
                        'cp_account_id' => 1,
                        'first_six' => "000000",
                        'last_four' => "0000",
                        'exp_date' => "00/00",
                        'token' => $data[0][0]['cardsId'],
                        'type' => 'pitech',
                        'name' => $data[0][0]['card']['owner'] ?? '',

                    ]);

                $product = Subscription::leftJoin('products', 'subscriptions.product_id', '=', 'products.id')
                   ->where('subscriptions.id', $data[0][0]['extOrdersId'] )
            ->where('subscriptions.status', 'trial')
            ->select('subscriptions.ended_at','products.*')
            ->orderBy('subscriptions.updated_at', 'desc')
            ->first();

              //  dd($product->ended_at);
                $trial_date =  Carbon::parse($data[0][0]['eventTime'])->addDays($product->trial_period);

                Subscription::where('id', $data[0][0]['extOrdersId'] )
                    ->where('status', 'trial')
                    ->update(['started_at' => $data[0][0]['eventTime'], 'ended_at' => $trial_date]);

                return response()->json([
                    'code' => 0,
                ], 200);
            }
            if ($data[0][0]['ordersState'] == 'ERROR'){

                UserLog::create([
                    'subscription_id' => $data[0][0]['extOrdersId'],
                    'user_id' => Auth::id(),
                    'type' => UserLog::SAVE_CARD_ERROR,
                    'data' => [
                        'new' => 'error',
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            \Log::info($data);
            \Log::error($e);
            return response()->json([
                'code' => 500
            ], 500);
        }
    }
}
