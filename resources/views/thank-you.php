<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<h4>Страница спасибо! </h4>

<?php

use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\ProductBonus;
use App\Models\Subscription;
use Carbon\Carbon;

$payments = \DB::table('pitech_notifications')
    ->whereNull('deleted_at')
    ->orderBy('id', 'desc')
    ->get();


foreach ($payments as $pay){
   //  print_r($payment->id);

    $result = json_decode($pay->data,true);
    //dd($result);

    $subscription = Subscription::whereId($result[0]['extOrdersId'])->first();

    if ($subscription){

        $payDate =  Carbon::parse($result[0]['ordersTime'])->setTimezone('Asia/Almaty');
        $newEndedAt =  Carbon::parse($result[0]['ordersTime'])->setTimezone('Asia/Almaty')->addMonths(1);

        $addSubscription = Subscription::where('id', $result[0]['extOrdersId'])
            //      ->where('id', $result[0]['extOrdersId'])
            ->limit(1)
            ->update(['status' => 'paid', 'ended_at' => $newEndedAt]);

        //   print_r($subscription->data);
        echo "<br><br>";
        echo  $result[0]['extOrdersId'];
        echo "<br><br>";
if($addSubscription){

   $payment = Payment::updateOrCreate([
            'transaction_id' => $result[0]['ordersId'],
        ],
        [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'product_id' => $subscription->product->id,
            'customer_id' => $subscription->customer->id,
            'quantity' => 1,
            'type' => 'pitech',
            'status' => 'Completed',
            'amount' => $result[0]['totalAmount'],
            'paided_at' => $result[0]['ordersTime'],
            'team_id' => $subscription->team_id,
            'data' =>  json_encode($result[0]),
           //     'pitech' =>  $result,
          //      'subscription' => $subscription,
          // ],
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
                echo "тут ".$pay->id."<br><br><br>";
                \DB::table('pitech_notifications')
                    ->where('id', $pay->id)
                    ->update(['deleted_at' => "2022-04-27 01:01:01"]);
            }
        }
        $payment->save();


            }
    }


    //   print_r($result[0]);

        echo $result[0]['eventTime'];
        echo "<br>";
        echo $result[0]['ordersTime'];
        echo "<br>";
        echo $result[0]['extOrdersId'];
        echo "<br>";
        echo $result[0]['extClientRef'];
        echo "<br>";
        echo $result[0]['description'];
        echo "<br>";
        echo $result[0]['ordersState'];
        echo "<br>";
        echo $result[0]['authAmount'];
        echo "<br><br><br>";

}

?>

</body>
</html>
