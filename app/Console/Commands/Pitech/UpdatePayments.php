<?php

namespace App\Console\Commands\Pitech;

use Illuminate\Console\Command;
use App\Models\Card;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\ProductBonus;
use App\Models\Subscription;
use App\Models\UserLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UpdatePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:pitech';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновелние оплат Pitech';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $payments = \DB::table('pitech_notifications')
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->get();

        $now = date("Y-m-d H:i:s");

        foreach ($payments as $pay){

            $result = json_decode($pay->data,true);

            if($result[0]['callbackType'] == 'FAIL'){

                $subscription = Subscription::whereId($result[0]['extOrdersId'])->first();
                if ($subscription){
                    $payDate =  Carbon::parse($result[0]['ordersTime'])->setTimezone('Asia/Almaty');

                    if($subscription->status != 'debtor'){
                        $addSubscription = Subscription::where('id', $result[0]['extOrdersId'])
                            ->limit(1)
                            ->update(['status' => 'waiting']);
                    }

                        \DB::table('pitech_notifications')
                            ->where('id', $pay->id)
                            ->update(['deleted_at' => $now]);

                        if(!Payment::where('transaction_id', $result[0]['ordersId'])->first()){
                            $paymentAdd =   \DB::table('payments')->insert([
                                'subscription_id' => $subscription->id,
                                'user_id' => $subscription->user_id,
                                'product_id' => $subscription->product->id,
                                'customer_id' => $subscription->customer->id,
                                'quantity' => 1,
                                'type' => 'pitech',
                                'status' => 'Declined',
                                'amount' => $result[0]['totalAmount'] ?? 0,
                                'paided_at' =>  $payDate,
                                // 'created_at' => $result[0]['eventTime'],
                                'team_id' => $subscription->team_id,
                                'data' =>  json_encode($result[0]),
                                'transaction_id' => $result[0]['ordersId'],
                            ]);

                            UserLog::create([
                                'subscription_id' => $subscription->id,
                                'user_id' => Auth::id(),
                                'type' => UserLog::SUBSCRIPTION_STATUS,
                                'data' => [
                                    'old' => $subscription->status,
                                    'new' => 'rejected',
                                ],
                            ]);


                        }
                }

            }
            else {
                $subscription = Subscription::whereId($result[0]['extOrdersId'])->first();
                $issetPay = Payment::where('transaction_id', $result[0]['ordersId'])->first();
                if ($subscription && !$issetPay){

                    $payDate =  Carbon::parse($result[0]['ordersTime'])->setTimezone('Asia/Almaty');
                    $newEndedAt =  Carbon::parse($subscription->ended_at)->setTimezone('Asia/Almaty')->addMonths(1);
                    if($subscription->payment_type != 'simple_payment'){
                        $addSubscription = Subscription::where('id', $result[0]['extOrdersId'])
                            //      ->where('id', $result[0]['extOrdersId'])
                            ->limit(1)
                            ->update(['status' => 'paid', 'payment_type' => 'pitech', 'ended_at' => $newEndedAt]);
                    }
                    else {
                        $addSubscription = Subscription::where('id', $result[0]['extOrdersId'])
                            //      ->where('id', $result[0]['extOrdersId'])
                            ->limit(1)
                            ->update(['status' => 'paid', 'ended_at' => $newEndedAt]);
                    }

                    \DB::table('pitech_notifications')
                        ->where('id', $pay->id)
                        ->update(['deleted_at' => $now]);

                    //   print_r($subscription->data);
                    if($addSubscription){

                        $paymentId = \DB::table('payments')->insertGetId([
                            'subscription_id' => $subscription->id,
                            'user_id' => $subscription->user_id,
                            'product_id' => $subscription->product->id,
                            'customer_id' => $subscription->customer->id,
                            'quantity' => 1,
                            'type' => 'pitech',
                            'status' => 'Completed',
                            'amount' => $result[0]['totalAmount'],
                            'paided_at' => $payDate,
                            // 'created_at' => $result[0]['eventTime'],
                            'team_id' => $subscription->team_id,
                            'data' =>  json_encode($result[0]),
                            'transaction_id' => $result[0]['ordersId'],
                        ]);

                        $payment = Payment::whereId($paymentId)->first();

                        //dd($payment->data);

                        Card::updateOrCreate([
                            'customer_id' => $payment->customer_id,
                            'token' => $payment->data['cardsId'],
                        ],
                            [
                                'customer_id' => $payment->customer_id,
                                'cp_account_id' => $payment->subscription_id ?? null,
                                'first_six' => "000000",
                                'last_four' => "0000",
                                'exp_date' => "00/00",
                                'token' => $payment->data['cardsId'],
                                'type' => 'pitech',
                                'name' => $payment->data['card']['owner'] ?? '',

                            ]);


                        // Присвоение бонуса к платежу
                        if (isset($payment->type) && isset($payment->subscription_id) && isset($payment->status) && $payment->status == 'Completed') {
                            $similarPaymentExists = $payment->subscription->payments()
                                ->where('status', 'Completed')
                                ->where('type', $payment->type)
                                ->where('paided_at', '<', $payment->paided_at)
                                ->where('id', '!=', $payment->id)
                                ->exists();
                            $type = $similarPaymentExists ? ProductBonus::REPEATED_PAYMENT : ProductBonus::FIRST_PAYMENT;


                            $bonus = ProductBonus::where('product_id', $payment->subscription->product_id)
                                ->where('payment_type_id', 2)
                                ->where('type', $type)
                                ->active()
                                ->first();
                            if (! $bonus) {
                                \Log::error('Отсутствует бонус. Payment ID: ' . $payment->id);
                            } else {
                                $payment->product_bonus_id = $bonus->id;

                                UserLog::create([
                                    'subscription_id' => $subscription->id,
                                    'user_id' => Auth::id(),
                                    'type' => UserLog::SUBSCRIPTION_STATUS,
                                    'data' => [
                                        'old' => $subscription->status,
                                        'new' => 'paid',
                                    ],
                                ]);
                            }
                        }


                        $payment->save();
                    }
                }
            }

        }
}
}
