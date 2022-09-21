<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSubscriptionRequest;
use App\Models\Price;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use App\Filters\SubscriptionFilter;
use App\Http\Resources\SubscriptionCollection;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Card;
use App\Models\Team;
use App\Models\UserLog;
use App\Services\CloudPaymentsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    private $root;
    private $perPage;

    public function __construct()
    {
        $this->root = 'subscriptions';
        $this->perPage = 45;
    }

    public function getList(SubscriptionFilter $filters)
    {
        access(['can-operator', 'can-head', 'can-host']);

        $query = Subscription::query();
        $subscriptions = $query->filter($filters)->latest()->paginate($this->perPage)->appends(request()->all());

        return response()->json(new SubscriptionCollection($subscriptions), 200);
    }

    public function getFilters()
    {
        access(['can-operator', 'can-head', 'can-host']);
       //   $products = Product::get()->pluck('title', 'id');
        $products = \DB::table('products')->whereNull('archived')->get()->pluck('title', 'id');
        $users = User::where('is_active','>','0')->get()->pluck('name', 'id')->toArray();
        $teams = Team::get()->pluck('name', 'id')->toArray();
        $prices = Price::groupBy('price')->orderBy('price', 'desc')->get('price')->pluck( 'price','price')->toArray();

        $teams[9999] = 'Без команды';

        $data['main'] = [
            // [
            //     'name' => 'customer_id',
            //     'title' => 'Клиенты',
            //     'type' => 'select-search',
            //     'key' => 'customer',
            //     'options' => [],
            // ],
            [
                'name' => 'product_id',
                'title' => 'Услуги',
                'type' => 'select-multiple',
                'options' => $products,
            ],
            [
                'name' => 'status',
                'title' => 'Статус абонемента',
                'type' => 'select-multiple',
                'options' => Subscription::STATUSES,
            ],
            [
                'name' => 'payment_type',
                'title' => 'Тип оплаты',
                'type' => 'select-multiple',
                'options' => Subscription::PAYMENT_TYPE,
            ],
//            [
//                'name' => 'team_id',
//                'title' => 'Команды',
//                'type' => 'select-multiple',
//                'options' => $teams,
//            ],
            [
                'name' => 'user_id',
                'title' => 'Оператор',
                'type' => 'select-multiple',
                'options' => $users,
            ],
            [
                'name' => 'price',
                'title' => 'Цена',
                'type' => 'select-multiple',
                'options' => $prices,
            ],
            [
                'name' => 'from_start_date',
                'title' => 'С даты старта',
                'type' => 'date',
            ],
            [
                'name' => 'to_start_date',
                'title' => 'По дату старта',
                'type' => 'date',
            ],
            [
                'name' => 'cp_subscription_id',
                'title' => 'Cloudpayment ID',
                'type' => 'input',
            ],
            [
                'name' => 'id',
                'title' => 'ID абонемента',
                'type' => 'input',
            ],

        ];

        $data['second'] = [
            [
                'name' => 'customer_name_or_phone',
                'placeholder' => 'Найти по имени и номеру',
                'title' => 'Клиенты',
                'type' => 'input-search',
                'key' => 'customer',
                'options' => [],
            ],
        ];

        return response()->json($data, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        access(['can-operator', 'can-head', 'can-host']);

        return view("{$this->root}.index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access(['can-operator', 'can-head', 'can-host']);
        $products = Product::get();
        $customers = Customer::get();

        return view("{$this->root}.create", [
            'products' => $products,
            'customers' => $customers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateSubscriptionRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateSubscriptionRequest $request, Subscription $subscription)
    {
        access(['can-operator', 'can-head', 'can-host']);

        $subscription->create($request->all());
        return redirect()->route("{$this->root}.index")->with('success', 'Абонемент успешно создан.');
    }

    /**
     * Display the specified resource.
     *
     * @param Subscription $subscription
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Subscription $subscription)
    {
        access(['can-operator', 'can-head', 'can-host']);

        return view("{$this->root}.show", [
            'subscription' => $subscription,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Subscription  $subscriptionId
     * @return \Illuminate\Http\Response
     */
    public function edit(Subscription $subscription)
    {
        access(['can-operator', 'can-head', 'can-host']);
        $products = Product::get();
        $customers = Customer::get();

        return view("{$this->root}.edit", [
            'subscription' => $subscription,
            'products' => $products,
            'customers' => $customers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CreateSubscriptionRequest $request
     * @param  \App\Models\Subscription  $subscription
     * @return \Illuminate\Http\Response
     */
    public function update(CreateSubscriptionRequest $request, Subscription $subscription)
    {
        access(['can-operator', 'can-head', 'can-host']);

        $subscription->update([
            'status' => $request->get('status'),
            'reason_id' => $request->get('reason_id'),
        ]);

        $message = 'Данные абонемента успешно изменены.';
        if ($request->ajax()) {
            return response()->json([
                'message' => $message,
            ]);
        } else {
            return redirect()->to(route("{$this->root}.show", [$subscription->id]))->with('success', $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Subscription $subscription
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Subscription $subscription, Request $request)
    {
        access(['can-operator', 'can-head', 'can-host']);

        $subscription->delete();

        $message = 'Абонемент успешно удален.';
        if ($request->ajax()) {
            return response()->json([
                'message' => $message,
            ]);
        } else {
            return redirect()->route("{$this->root}.index")->with('success', $message);
        }
    }

    public function manualWriteOffPayment(Request $request)
    {
        $subscriptionId = $request->get('subscriptionId');
        $subscription = Subscription::whereId($subscriptionId)->whereNotNull('cp_subscription_id')->first();
        $now = Carbon::now();
        $endedAt = Carbon::parse($subscription->ended_at);
        if ($endedAt > $now) {
            throw new \Exception('Ошибка! Обратитесь к менеджеру или администратору.', 500);
        }

        $manualWriteOffAt = isset($subscription->manual_write_off_at) ? $subscription->manual_write_off_at : Carbon::yesterday();
        $manualWriteOffPlusMinutes = Carbon::parse($manualWriteOffAt)->addMinutes(2);
        $now = Carbon::now();
        if ($manualWriteOffPlusMinutes > $now) {
            $diffSeconds = $manualWriteOffPlusMinutes->diffInSeconds($now);
            throw new \Exception('Попробуйте через ' . $diffSeconds . ' секунд.', 500);
        }

        $cloudpaymentService = new CloudPaymentsService();
        try {
            $createPayment =  $this->writeOffPaymentByToken($request);
            UserLog::create([
                'subscription_id' => $subscription->id,
                'user_id' => Auth::id(),
                'type' => UserLog::MANUAL_WRITE_OFF,
                'data' => [],
            ]);
            $date_start = Carbon::parse($subscription->ended_at)->addMonth();
            $new_date = Carbon::parse($date_start)->format('Y-m-d H:i:s');

            $subscription->update([
                'manual_write_off_at' => Carbon::now(),
                'status' => 'waiting',
                'ended_at' => $new_date,
            ]);

            $cloudpaymentService->updateSubscription([
                'Id' => $subscription->cp_subscription_id,
                'StartDate' => $new_date,
                'Description' => 'Дата списания: ' . Carbon::yesterday()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            throw new \Exception('Ошибка при запросе на ручное списание денег. Попробуйте позднее', 500);
        }

        return response()->json([
            'message' => 'Запрос на списание отправлен.'
        ], 200);
    }

    public function writeOffPaymentByToken(Request $request)
    {
        $subscriptionId = $request->get('subscriptionId');
        $cardId = $request->get('cardId');
        $subscription = Subscription::whereId($subscriptionId)->firstOrFail();
        $card = $subscription->customer->cards()->whereId($cardId)->firstOrFail();

        $manualWriteOffAt = isset($subscription->manual_write_off_at) ? $subscription->manual_write_off_at : Carbon::yesterday();
        $manualWriteOffPlusMinutes = Carbon::parse($manualWriteOffAt)->addMinutes(2);
        $now = Carbon::now();
        if ($manualWriteOffPlusMinutes > $now) {
            $diffSeconds = $manualWriteOffPlusMinutes->diffInSeconds($now);
            throw new \Exception('Попробуйте через ' . $diffSeconds . ' секунд.', 500);
        }

        $cloudpaymentService = new CloudPaymentsService();
        try {
            UserLog::create([
                'subscription_id' => $subscription->id,
                'user_id' => Auth::id(),
                'type' => UserLog::MANUAL_WRITE_OFF,
                'data' => [],
            ]);
            $subscription->update([
                'manual_write_off_at' => Carbon::now(),
            ]);
            $cloudpaymentService->paymentsTokensCharge([
                'Amount' => $subscription->price,
                'Currency' => 'KZT',
                'AccountId' => $card->cp_account_id,
                'Token' => $card->token,
                'Description' => 'Оплата услуги - ' . $subscription->product->title,
                'JsonData' => [
                    'cloudPayments' => [
                        'customerReceipt' => [
                            'Items' => [ //товарные позиции
                                [
                                    'label' => $subscription->product->title, // наименование товара
                                    'price' => $subscription->price, // цена
                                    'quantity' => 1.00, //количество
                                    'amount' => $subscription->price, // сумма
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
                        'id' => $subscription->product_id,
                    ],
                    'subscription' => [
                        'id' => $subscription->id,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            throw new \Exception('Ошибка при запросе на ручное списание денег. Попробуйте позднее', 500);
        }

        return response()->json([
            'message' => 'Запрос на списание отправлен. Обновите страницу через минуту.'
        ], 200);
    }

    public function getDetail(Request $request){
        $details = \DB::select("
                    SELECT
                      customers.phone,
                      customers.name as customer_name,
                      subscriptions.started_at,
                      subscriptions.status,
                      subscriptions.price,
                      products.title,
                      products.description,
                      users.id,
                      users.account,
                      users.name,
                      subscriptions.payment_type,
                      subscriptions.ended_at
                    FROM subscriptions
                      INNER JOIN customers
                        ON subscriptions.customer_id = customers.id
                      INNER JOIN products
                        ON subscriptions.product_id = products.id
                      INNER JOIN users
                        ON subscriptions.user_id = users.id
                    WHERE subscriptions.id = '".$request->id."'
        ");

        return $details;
    }
}
