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

use App\Models\Subscription;
use Carbon\Carbon;

$payments = \DB::table('pitech_notifications')
    ->orderBy('id', 'desc')
    ->get();



foreach ($payments as $payment){
    // print_r($payment->data);

    $result = json_decode($payment->data,true);
    $subscription = Subscription::whereId($result[0]['extOrdersId'])->first();

    if ($subscription){
        $payDate =  Carbon::parse($result[0]['ordersTime'])->setTimezone('Asia/Almaty');
        $newEndedAt =  Carbon::parse($result[0]['ordersTime'])->setTimezone('Asia/Almaty')->addMonths(1);

       Subscription::where('id', $result[0]['extOrdersId'])
      //      ->where('id', $result[0]['extOrdersId'])
            ->limit(1)
           ->update(['status' => 'paid', 'ended_at' => $newEndedAt]);

     //   print_r($subscription->data);
        echo "<br><br>";
        echo  $result[0]['extOrdersId'];
        echo "<br><br>";

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
