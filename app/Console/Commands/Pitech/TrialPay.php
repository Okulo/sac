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
use App\Models\Product;

class TrialPay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trial:pitech';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Оплата триальных карт Pitech';

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
        $subscription = Subscription::where('payment_type', 'tries')
            ->where('status','trial')
            ->leftJoin('products', 'subscriptions.product_id', '=', 'products.id')
            ->select('subscriptions.*', 'products.title AS ptitle')
            ->groupBy('customer_id')
            ->get();


        foreach($subscription as $subscription)
        {
            $card = Card::where('type', 'pitech')
                ->where('customer_id', $subscription['customer_id'])
                ->latest()
                ->first();

            $endSubscr = Carbon::parse($subscription->ended_at)->setTimezone('Asia/Almaty')->format('Y-m-d');
            $endSubscr1day = Carbon::parse($endSubscr)->addDay()->format('Y-m-d');
            $endSubscr2day = Carbon::parse($endSubscr)->addDays(2)->format('Y-m-d');
            $today = Carbon::now()->setTimezone('Asia/Almaty')->startOfDay()->format('Y-m-d');

            if(($card) && ($today == $endSubscr || $today == $endSubscr1day || $today == $endSubscr2day)){

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    // тестовый режим
                    CURLOPT_URL => 'https://cards-stage.pitech.kz/gw/payments/tokens/charge',
                    // боевой
                   //  CURLOPT_URL => 'https://cards.pitech.kz/gw/payments/tokens/charge',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>'{
                    "amount": '.$subscription->price.',
                    "currency": "KZT",
                    "description": "'.$subscription->ptitle.'",
                    "extClientRef": "'.$subscription->customer_id.'",
                    "extOrdersId": "'.$subscription->id.'",
                    "errorReturnUrl": "https://www.strela-academy.ru/api/pitech/pay-fail",
                    "successReturnUrl": "https://www.strela-academy.ru/thank-you",
                    "callbackSuccessUrl": "http://test.strela-academy.ru/api/pitech/pay-success",
                    "callbackErrorUrl": "http://test.strela-academy.ru/api/pitech/pay-success",
                    "fiscalization": true,
                    "positions":[
                        {
                        "count": 1,
                        "unitName": "pc",
                        "price": '.$subscription->price.',
                        "name": "'.$subscription->ptitle.'"
                        }
                    ],
                    "cardsId": "'.$card->token.'"
                }
                ',
                    CURLOPT_HTTPHEADER => array(
                       'Authorization: Basic c2RJY2hNS3VTcVpza3BFOVdvVC1nSG9jSnhjd0xrbjY6WmxwYVJZTkFDbUJhR1Utc0RpRFEzUVM1RFhVWER0TzI=',
                        //бой
                        // 'Authorization: Basic NjBQWS1MWnluZGNQVl9LQzhjTm5tZW9oLTg2c2Y1MHA6VVA3WWxEa3pzZ3pYS2p2T2dMNjQxdEpOOFpnTUhEWXY=',
                        'Content-Type: application/json'
                    ),
                ));

                $response = curl_exec($curl);
                curl_close($curl);
                print_r($response);

            }
        }
    }

}
