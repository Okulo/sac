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

$payments = \DB::table('pitech_notifications')
    ->orderBy('id', 'desc')
    ->get();



foreach ($payments as $payment){
    // print_r($payment->data);

    $result = json_decode($payment->data,true);

    foreach ($result as  $res){
        echo $res['eventTime'];
        echo "<br>";
        echo $res['ordersTime'];
        echo "<br>";
        echo $res['extOrdersId'];
        echo "<br>";
        echo $res['extClientRef'];
        echo "<br>";
        echo $res['description'];
        echo "<br>";
        echo $res['ordersState'];
        echo "<br>";
        echo $res['authAmount'];
        echo "<br><br><br>";

    }
}

?>

</body>
</html>
