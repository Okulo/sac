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

class PaySubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pay:pitech';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Оплата подписки Pitech';

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
        $payments = Payment::where('type', 'pitech')->get();
        $subscriptions = Subscription::where('payment_type', 'pitech')->where('status','waiting')->get();


        foreach ($subscriptions as $subscription){
            $product = Product::whereId($subscription['product_id'])->get();
            $endSubscr = Carbon::parse($subscription['ended_at'])->setTimezone('Asia/Almaty')->format('Y-m-d');
            $endSubscr1day = Carbon::parse($endSubscr)->addDay()->format('Y-m-d');
            $endSubscr2day = Carbon::parse($endSubscr)->addDays(2)->format('Y-m-d');
            $today = Carbon::now()->setTimezone('Asia/Almaty')->startOfDay()->format('Y-m-d');
            $yesterday = Carbon::now()->setTimezone('Asia/Almaty')->subDays(1)->format('Y-m-d');
            $newEndedAt =  $newEndedAt =  Carbon::parse($subscription['ended_at'])->setTimezone('Asia/Almaty')->addMonths(1);


            $card = Card::where('type', 'pitech')
                ->where('customer_id', $subscription['customer_id'])
                ->latest()
                ->first();

            if(isset($card)){
                foreach($product as $obj)
                {
                    $productTitle = $obj['title'];
                }
                if($today == $endSubscr || $today == $endSubscr1day || $today == $endSubscr2day){

                    $data =  $this->pitechSubscriptionPayment ($subscription['price'], $productTitle, $subscription['customer_id'], $subscription['id'], $card['token'] );
                    // $data = '{"merchantName":"Strela-Academy","extOrdersId":"21223","amount":2990.0,"uuid":"f84d5c0f-e53e-4a07-97cd-1ddd2c776880","orderDate":"2022-05-27","ordersId":"120983513568625","currency":"KZT","state":"SUCCESS","card":{"mask":"548318-######-0293","owner":"RASHID","issuer":"mastercard"},"bankReferenceId":"220527193442","bankReferenceTime":"2022-05-27T19:34:42.859+0600","approvalCode":"193442","paymentResponseCode":"OK","paymentUrl":"https://cards-stage.pitech.kz/pay/order?ordersId=120983513568625&orderDate=2022-05-27&uuid=f84d5c0f-e53e-4a07-97cd-1ddd2c776880","successReturnUrl":"http://test.strela-academy.ru/thank-you","errorReturnUrl":"http://test.strela-academy.ru/api/pitech/pay-fail","clientsId":1358,"extClientRef":"19331","cardsId":"card_sJkRp8spG-pd0AA5tHyHLPaCJTNJW_fu","payload":{},"ipDetails":{"ip":"37.99.42.217","continentCode":"AS","continentName":"Asia","countryCode":"KZ","countryName":"Kazakhstan","regionCode":"ALA","regionName":"Almaty","zip":"480000","latitude":43.2499885559082,"longitude":76.94998931884766},"description":"Йога + пилатес (Онлайн тренировки)","paymentType":"RECURRENT","expirationTime":"2022-05-27T19:49:39.503","merchantsId":1043,"fiscalization":false,"linkedOrders":[]}';
                    $result = json_decode($data);
                    if(isset($result->state)){
                        if($result->state == 'SUCCESS'){
                            UserLog::create([
                                'subscription_id' => $subscription['id'],
                                'user_id' => Auth::id() ?? null,
                                'type' => UserLog::PITECH_AUTO_RENEWAL,
                                'data' => [
                                    'old' => $subscription['ended_at'],
                                    'new' => $newEndedAt,
                                    'request' => $result,
                                ],
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function pitechSubscriptionPayment($price, $product, $customer, $subscrId, $card ){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            //тестовый режим
            //CURLOPT_URL => 'https://cards-stage.pitech.kz/gw/payments/tokens/charge',
            // боевой
             CURLOPT_URL => 'https://cards.pitech.kz/gw/payments/tokens/charge',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                    "amount": '.$price.',
                    "currency": "KZT",
                    "description": "'.$product.'",
                    "extClientRef": "'.$customer.'",
                    "extOrdersId": "'.$subscrId.'",
                    "errorReturnUrl": "https://www.strela-academy.ru/api/pitech/pay-fail",
                    "successReturnUrl": "https://www.strela-academy.ru/thank-you",
                    "callbackSuccessUrl": "https://www.strela-academy.ru/api/pitech/pay-success",
                    "callbackErrorUrl": "https://www.strela-academy.ru/api/pitech/pay-success",
                    "fiscalization": true,
                    "positions":[
                    {
                    "count": 1,
                    "unitName": "pc",
                    "price": '.$price.',
                    "name": "'.$product.'"
                    }
                ],
                    "cardsId": "'.$card.'"
                }
                ',
            CURLOPT_HTTPHEADER => array(
                //'Authorization: Basic c2RJY2hNS3VTcVpza3BFOVdvVC1nSG9jSnhjd0xrbjY6WmxwYVJZTkFDbUJhR1Utc0RpRFEzUVM1RFhVWER0TzI=',
                //бой
                'Authorization: Basic NjBQWS1MWnluZGNQVl9LQzhjTm5tZW9oLTg2c2Y1MHA6VVA3WWxEa3pzZ3pYS2p2T2dMNjQxdEpOOFpnTUhEWXY=',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}
