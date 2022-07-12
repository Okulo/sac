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
            $data =  $request->all();

            if($data['ordersState'] == 'SUCCESS') {
                Card::updateOrCreate([
                    'customer_id' => $data['extClientRef'],
                    'token' => $data['cardsId'],
                ],
                    [
                        'customer_id' => $data['extClientRef'],
                        'cp_account_id' => 1,
                        'first_six' => "000000",
                        'last_four' => "0000",
                        'exp_date' => "00/00",
                        'token' => $data['cardsId'],
                        'type' => 'pitech',
                        'name' => $data['card']['owner'] ?? '',

                    ]);

                $product = Subscription::leftJoin('products', 'subscriptions.product_id', '=', 'products.id')
                   ->where('subscriptions.id', $data['extOrdersId'] )
            ->where('subscriptions.status', 'trial')
            ->select('subscriptions.ended_at','products.*')
            ->orderBy('subscriptions.updated_at', 'desc')
            ->first();

              //  dd($product->ended_at);
                $trial_date =  Carbon::parse($data['eventTime'])->addDays($product->trial_period);

                Subscription::where('id', $data['extOrdersId'] )
                    ->where('status', 'trial')
                    ->update(['started_at' => $data['eventTime'], 'ended_at' => $trial_date]);

                UserLog::create([
                    'subscription_id' => $data['extOrdersId'],
                    'user_id' => Auth::id(),
                    'type' => UserLog::SAVE_CARD_SUCCESS,
                    'data' => [
                        'date' => $data['eventTime'],
                    ],
                ]);

                return response()->json([
                    'code' => 0,
                ], 200);
            }
            if ($data['ordersState'] == 'ERROR'){

                UserLog::create([
                    'subscription_id' => $data['extOrdersId'],
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
