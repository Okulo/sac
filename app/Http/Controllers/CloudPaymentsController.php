<?php

namespace App\Http\Controllers;

use App\Http\Resources\ThankYouProductResource;
use App\Models\Product;
use App\Models\Subscription;
use App\Services\CloudPaymentsService;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;

class CloudPaymentsController extends Controller
{
    public function showWidget(int $subscriptionId, Request $request)
    {
        $subscription = Subscription::whereId($subscriptionId)
            ->whereNull('cp_subscription_id')
            ->whereIn('payment_type', ['cloudpayments', 'simple_payment'])
            ->where('status', '!=', 'paid')
            ->firstOr(function () {
                abort(404);
            });

        $payment = $subscription->payments()->whereStatus('new')->whereType($subscription->payment_type)->first();

        // Если у юзера вышла ошибка с платежом, создаем новый
        if (!$payment) {
            $lastPayment = $subscription->payments()->latest()->whereNotNull('user_id')->whereType('cloudpayments')->first();
            $payment = $subscription->payments()->create([
                'customer_id' => $subscription->customer->id,
                'product_id' => $subscription->product->id,
                'user_id' => $lastPayment->user_id ?? null,
                'type' => $subscription->payment_type,
                'status' => 'new',
                'amount' => $subscription->price,
                'quantity' => 1,
                'paided_at' => Carbon::now(),
            ]);
        } else {
            $payment->update([
                'amount' => $subscription->price,
                'type' => $subscription->payment_type,
            ]);
        }

        $publicId = env('CLOUDPAYMENTS_USERNAME');
        $data = [
            'publicId' => $publicId, //id из личного кабинета
            'description' => '', //назначение
            'amount' => $payment->amount, //сумма
            'currency' => 'KZT', //валюта
            'email' => null, // Email
            'skin' => "modern",
            'accountId' => $subscription->id, //идентификатор плательщика (обязательно для создания подписки)
            'data' => [
                'cloudPayments' => [
                    'customerReceipt' => [
                        'Items' => [ //товарные позиции
                            [
                                'label' => $subscription->product->title, // наименование товара
                                'price' => $payment->amount, // цена
                                'quantity' => 1.00, //количество
                                'amount' => $payment->amount, // сумма
                                'vat' => 0, // ставка НДС
                                'method' => 0, // тег-1214 признак способа расчета - признак способа расчета
                                'object' => 0, // тег-1212 признак предмета расчета - признак предмета товара, работы, услуги, платежа, выплаты, иного предмета расчета
                                'measurementUnit' => "шт" //единица измерения
                            ],
                        ],
                        'calculationPlace' => "www.strela-academy.ru", //место осуществления расчёта, по умолчанию берется значение из кассы
                        'taxationSystem' => 0, //система налогообложения; необязательный, если у вас одна система налогообложения
                        'email' => $subscription->customer->email, //e-mail покупателя, если нужно отправить письмо с чеком
                        'phone' => $subscription->customer->phone, //телефон покупателя в любом формате, если нужно отправить сообщение со ссылкой на чек
                        'isBso' => false,
                    ],
                ],
                'product' => [
                    'id' => $payment->subscription->product->id,
                ],
                'subscription' => [
                    'id' => $payment->subscription->id,
                ],
            ],
        ];

        if ($subscription->payment_type == 'cloudpayments') {
            $data['description'] = $payment->subscription->product->title;
            $data['data']['cloudPayments']['recurrent'] = [
                'interval' => 'Month',
                'period' => 1,
                'customerReceipt' => [
                    'Items' => [ //товарные позиции
                        [
                            'label' => $subscription->product->title, // наименование товара
                            'price' => $payment->amount, // цена
                            'quantity' => 1.00, //количество
                            'amount' => $payment->amount, // сумма
                            'vat' => 0, // ставка НДС
                            'method' => 0, // тег-1214 признак способа расчета - признак способа расчета
                            'object' => 0, // тег-1212 признак предмета расчета - признак предмета товара, работы, услуги, платежа, выплаты, иного предмета расчета
                            'measurementUnit' => "шт" //единица измерения
                        ],
                    ],
                    'taxationSystem' => 0, //система налогообложения; необязательный, если у вас одна система налогообложения
                    'email' => $subscription->customer->email, //e-mail покупателя, если нужно отправить письмо с чеком
                    'phone' => $subscription->customer->phone, //телефон покупателя в любом формате, если нужно отправить сообщение со ссылкой на чек
                    'isBso' => false,
                ],
            ];
        } else if ($subscription->payment_type == 'simple_payment') {
            $data['description'] = 'Покупка услуги - ' . $subscription->product->title;
        }

        return view('cloudpayments.show-widget', [
            'payment' => $payment,
            'data' => json_encode($data),
        ]);
    }

    public function thankYou(int $productId, Request $request)
    {
        $product = Product::whereId($productId)->firstOr(function () {
            abort(404);
        });

        $products = $product->additionals;

        return view('cloudpayments.thank-you', [
            'product' => $product,
            'products' => ThankYouProductResource::collection($products),
        ]);
    }

    public function updateAmount( Request $request)
    {

        // ниже нужно добавить информациюя по чеку

        //                    Amount: "3990"
        //                    cpId: "sc_11a38e8375ee3c6b1c7756312e0cd"
        //                    product: "Антижир (Активные онлайн тренировки)"
        //                    subscriptionId: 17933


        $cloudpaymentService = new CloudPaymentsService();
        $data =  $cloudpaymentService->updateSubscription([
            'Id' => $request->cpId,
            'Amount' => $request->Amount,
            'customerReceipt' => [
                'Items' => [ //товарные позиции
                    [
                        'label' => $request->product, // наименование товара
                        'price' => $request->Amount, // цена
                        'quantity' => 1.00, //количество
                        'amount' => $request->Amount, // сумма
                        'vat' => 0, // ставка НДС
                        'method' => 0, // тег-1214 признак способа расчета - признак способа расчета
                        'object' => 0, // тег-1212 признак предмета расчета - признак предмета товара, работы, услуги, платежа, выплаты, иного предмета расчета
                        'measurementUnit' => "шт" //единица измерения
                    ],
                ],
                'calculationPlace' => "www.strela-academy.ru", //место осуществления расчёта, по умолчанию берется значение из кассы
                'taxationSystem' => 0, //система налогообложения; необязательный, если у вас одна система налогообложения
                'email' => '', //e-mail покупателя, если нужно отправить письмо с чеком
                'phone' => '', //телефон покупателя в любом формате, если нужно отправить сообщение со ссылкой на чек
                'isBso' => false,
                'amounts' => [
                    'electronic' =>  $request->Amount // Сумма оплаты электронными деньгами
                ]
            ],
        ]);

        return $data;

    }
    public function saveCpResponse( Request $request)
    {
        $datas=json_encode($request->model);
        \DB::table('cp_response')->insert([
            'success' => $request->success,
            'model' => $datas,
            'cp_subscription_id' => $request->subsid
        ]);
    }

    // public function showCheckout(int $subscriptionId, Request $request)
    // {
    //     $subscription = Subscription::whereId($subscriptionId)->where('status', '!=', 'paid')->firstOr(function () {
    //         abort(404);
    //     });

    //     $payment = $subscription->payments()->whereStatus('new')->whereType('cloudpayments')->first();

    //     // Если у юзера вышла ошибка с платежом, создаем новый
    //     if (!isset($payment)) {
    //         $payment = $subscription->payments()->create([
    //             'customer_id' => $subscription->customer->id,
    //             'user_id' => null,
    //             'quantity' => 1,
    //             'type' => 'cloudpayments',
    //             'status' => 'new',
    //             'amount' => $subscription->price,
    //         ]);
    //     } else {
    //         $payment->update([
    //             'amount' => $subscription->price,
    //         ]);
    //     }

    //     $publicId = env('CLOUDPAYMENTS_USERNAME');

    //     return view('cloudpayments.show-checkout', [
    //         'payment' => $payment,
    //         'customer' => $subscription->customer,
    //         'subscription' => $subscription,
    //         'product' => $subscription->product,
    //         'publicId' => $publicId,
    //     ]);
    // }
}
