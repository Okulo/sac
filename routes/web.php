<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UsersBonusesController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\ReportController;
use App\Models\Bonus;
use App\Models\Card;
use App\Models\Chart;
use App\Models\Customer;
use App\Models\Graph;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StatisticsModel;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get("/test", function () {
    $notifications = Notification::whereNotNull('subscription_id')->get();

    foreach ($notifications as $notification) {
        if (isset($notification->subscription)) {
            $notification->update([
                'team_id' => $notification->subscription->team_id,
            ]);
        }
    }
})->name("test");

Route::get("/test2", function () {
    $payments = Payment::whereType('cloudpayments')->whereStatus('Completed')->get();
    foreach ($payments as $payment) {
        if (isset($payment->data['cloudpayments']) && isset($payment->data['cloudpayments']['Token']) && isset($payment->customer)) {
            Card::updateOrCreate([
                'customer_id' => $payment->customer->id,
                'token' => $payment->data['cloudpayments']['Token'],
            ],
            [
                'customer_id' => $payment->customer->id,
                'cp_account_id' => $payment->data['cloudpayments']['AccountId'] ?? null,
                'token' => $payment->data['cloudpayments']['Token'],
                'first_six' => $payment->data['cloudpayments']['CardFirstSix'] ?? null,
                'last_four' => $payment->data['cloudpayments']['CardLastFour'] ?? null,
                'exp_date' => $payment->data['cloudpayments']['CardExpDate'] ?? null,
                'type' => $payment->data['cloudpayments']['CardType'] ?? null,
                'name' => $payment->data['cloudpayments']['Name'] ?? '',

            ]);
        }
    }
})->name("test2");

Route::get("/test3", function () {
    $subscriptions = Subscription::
        // whereNull('team_id')
        // ->whereStatus('paid')
        whereNotIn('product_id', [1, 3])
        ->get();
        // ->groupBy('product_id');

    $teams = [
        'zhir-v-minus' => 1,
        'yoga-lates' => 2
    ];

    // foreach ($productsSubscriptions as $productId => $subscriptions) {
        foreach ($subscriptions as $subscription) {
            $subs = $subscription->customer->subscriptions->where('team_id', '!=', null)->where('product_id', '!=', $subscription->productId);
            $subscriptionCount = $subs->count();

            if ($subscriptionCount == 0) {
                $team = rand(1,2);
            } else if ($subscriptionCount == 1) {
                $team = $subs->first()->team_id;
            } else {
                $team = $subs->first()->team_id;
            }

            $subscription->update([
                'team_id' => $team,
                // 'team_id' => $teams[$subscription->product->code],
            ]);
        }
    // }
})->name("test3");

Route::get("/test4", function () {
    $payments = Payment::
        whereStatus('Completed')
        ->whereNull('team_id')
        ->get();
    $paymentIds = [];
    foreach ($payments as $payment) {
        if (isset($payment->subscription) && isset($payment->subscription->product)) {
            $payment->update([
                'team_id' => $payment->subscription->team_id,
            ]);
        } else {
            $paymentIds[] = $payment->id;
            // dd($payment, $payment->subscription, $payment->product);
        }
    }

    dd($paymentIds);
})->name("test4");

Route::get("/test5", function () {
    $subscriptions = Subscription::whereHas('payments', function ($q) {
        $q->where('status', 'Completed');
    })->where('status', 'refused')->whereBetween('updated_at', [
        Carbon::parse('2021-06-20 00:00:00'),
        Carbon::parse('2021-06-27 23:59:59'),
    ])->get();

    foreach ($subscriptions as $subscription) {
        $subscription->update([
            'updated_at' => Carbon::parse('2020-11-00 00:00:00')
        ]);
    }
    // dd($subscriptions->count());
})->name("test5");

Route::get("/test6", function () {
    $from = Carbon::parse('2021-04-01 00:00:00');
    $to = Carbon::now();
    $payments = Payment::where('status', 'Completed')->where('type', 'transfer')->whereBetween('paided_at', [$from, $to])->get()
        ->groupBy('subscription_id')
        ->transform(function($items) {
            return $items->groupBy(function($payment) {
                return Carbon::parse($payment->paided_at)->format('Y-m');
            });
        })->toArray();
    $data = [];
    foreach ($payments as $subId => $times) {
        foreach ($times as $spayments) {
            if (count($spayments) > 1) {
                $data[] = $subId;
            }
        }
    }
    dd($data);
})->name("test6");

Route::get("/", [HomeController::class, "homepage"])->name("homepage");
Route::get("/thank-you", [HomeController::class, "thankYou"])->name("thankYou");
Route::get("/card-saved", [HomeController::class, "cardSaved"])->name("cardSaved");
Route::get("/card-save-fail", [HomeController::class, "cardSaveFail"])->name("cardSaveFail");
Route::get("/failure", [HomeController::class, "failure"])->name("failure");
Auth::routes();

Route::middleware(["auth", 'auth.user'])->group(function () {
    Route::get("/dashboard", [HomeController::class, "dashboard"])->name("dashboard");
    Route::post("/statistics", [StatisticsController::class, "update"])->name("statistics.update");
    Route::post("/statistics/timeline", [StatisticsController::class, "updateTimeline"])->name("statistics_timeline.update");
    Route::get("/statistics/quantitative", [StatisticsController::class, "quantitative"])->name("statistics.quantitative");
    Route::get("/statistics/financial", [StatisticsController::class, "financial"])->name("statistics.financial");
    Route::get("/search", 'SearchController@search')->name("search");

    Route::get("/users-bonuses", [UsersBonusesController::class, "show"])->name("users_bonuses.show");
    Route::get("/manager-bonuses", [UsersBonusesController::class, "showManagerBonuses"])->name("manager_bonuses.show");

    Route::post('customers/update-with-data', 'CustomerController@createWithData');
    Route::get('customers/list', 'CustomerController@getList');
    Route::get('customers/get-options', 'CustomerController@getOptions');
    Route::get('customers/{customerId}/with-data', 'CustomerController@getCustomerWithData');
    Route::get('customers/filter', 'CustomerController@getFilters');
    Route::post('/customers/get-customer-card', 'CustomerController@getCustomerCard');

    Route::get('products/list', 'ProductController@getList');
    Route::get('products/filter', 'ProductController@getFilters');

    Route::get('teams/list', 'TeamController@getList');
    Route::get('teams/filter', 'TeamController@getFilters');

    Route::get('users/list', 'UserController@getList');
    Route::get('users/filter', 'UserController@getFilters');
    Route::get('users/change/{subId}', 'UserController@changeOperator');

    Route::get('notifications/list', 'NotificationController@getList');
    Route::get('notifications/filter', 'NotificationController@getFilters');

    Route::get('subscriptions/list', 'SubscriptionController@getList');
    Route::get('subscriptions/filter', 'SubscriptionController@getFilters');
    Route::post('subscriptions/manualWriteOffPayment', 'SubscriptionController@manualWriteOffPayment');
    Route::post('subscriptions/writeOffPaymentByToken', 'SubscriptionController@writeOffPaymentByToken');
    Route::post('subscriptions/getDetail', 'SubscriptionController@getDetail');

    Route::get('userlogs/list', 'UserLogController@getList');
    Route::get('userlogs/filter', 'UserLogController@getFilters');

    Route::get('payments/list', 'PaymentController@getList');
    Route::get('payments/filter', 'PaymentController@getFilters');

    Route::get('products/with-prices', 'ProductController@withPrices');
    Route::post('products/archive-product', 'ProductController@archiveProduct');
    Route::post('products/restore-product', 'ProductController@restoreProduct');

    Route::get('/reports/get-reports/', 'ReportController@index');
    Route::get('/reports/get-reports/{type}', 'ReportController@index');
    Route::post("/reports/get-list", [ReportController::class, "getList"])->name("reports.getList");
    Route::post("/reports/get-pay-list", [ReportController::class, "getPayList"])->name("reports.getPayList");
    Route::post("/reports/simple-pay-ends-list", [ReportController::class, "simplePayEndsList"])->name("reports.simplePayEndsList");
    Route::post("/reports/get-pay-error-list", [ReportController::class, "getPayErrorList"])->name("reports.getPayErrorList");
    Route::post("/reports/get-cp-pay-errors", [ReportController::class, "getCpPayErrors"])->name("reports.getCpPayErrors");
    Route::post("/reports/get-refused-list", [ReportController::class, "getRefusedList"])->name("reports.getRefusedList");
    Route::post("/reports/get-processed-status", [ReportController::class, "getProcessedStatus"])->name("reports.getProcessedStatus");
    Route::post("/reports/get-waiting-pay-list", [ReportController::class, "getWaitingPay"])->name("reports.getWaitingPay");
    Route::post("/reports/get-debtors-list", [ReportController::class, "getDebtorsList"])->name("reports.getDebtorsList");
    Route::post("/reports/get-archived-products", [ReportController::class, "getArchivedProducts"])->name("reports.getArchivedProducts");
    Route::post("/reports/get-refused-subscriptions-list", [ReportController::class, "getRefusedSubscriptionsList"])->name("reports.getRefusedSubscriptionsList");
    Route::post("/reports/getSubscription", [ReportController::class, "getSubscription"])->name("reports.getSubscription");
    Route::post("/reports/add-wa-status", [ReportController::class, "addWaStatus"])->name("reports.addWaStatus");
    Route::post("/reports/set-processed-status", [ReportController::class, "setProcessedStatus"])->name("reports.setProcessedStatus");
    Route::post("/reports/save-status", [ReportController::class, "saveStatus"])->name("reports.saveStatus");
    Route::post("/reports/get-user-payments", [ReportController::class, "getUserPayments"])->name("reports.getUserPayments");
    Route::post("/reports/get-user-bonus", [ReportController::class, "getUserBonus"])->name("reports.getUserBonus");
    Route::get("/reports/operator-bonus-details/{id?}", [ReportController::class, "operatorBonusDetail"])->name("reports.operatorBonusDetail");
    Route::post("/reports/get-operator-summ", [ReportController::class, "getOperatorSumm"])->name("reports.getOperatorSumm");
    Route::post("/reports/get-subscriptions", [ReportController::class, "getSubscriptions"])->name("reports.getSubscriptions");
    Route::post("/reports/get-all-subscriptions", [ReportController::class, "getAllSubscriptions"])->name("reports.getAllSubscriptions");


    Route::resources([
        'customers' => 'CustomerController',
        'products' => 'ProductController',
        'teams' => 'TeamController',
        'subscriptions' => 'SubscriptionController',
        'payments' => 'PaymentController',
        'users' => 'UserController',
        'notifications' => 'NotificationController',
        'userlogs' => 'UserLogController',
    ]);
});

Route::get("/pull", [HomeController::class, "pull"])->name("pull");
Route::get('cloudpayments/{subscriptionId}', 'CloudPaymentsController@showWidget')->name('cloudpayments.show_widget');
Route::get('pitech/{subscriptionId}', 'CloudPaymentsController@showPitechWidget')->name('cloudpayments.show_pitech_widget');
Route::post('pitech/manualPayment', 'CloudPaymentsController@manualPitechPayment')->name('pitech.manual');
Route::post('pitech/payWithCard', 'CloudPaymentsController@payPitechWithCard')->name('pitech.payWithCard');
Route::post('pitech/get-customer-id', 'CloudPaymentsController@getCustomerId')->name('pitech.getCustomerId');
Route::get('cloudpayments/{productId}/thank-you', 'CloudPaymentsController@thankYou')->name('cloudpayments.thank_you');
Route::post('cloudpayments/updateamount', 'CloudPaymentsController@updateAmount')->name('cloudpayments.update');

