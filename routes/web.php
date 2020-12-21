<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Models\Customer;
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
Route::get("/", [HomeController::class, "homepage"])->name("homepage");
Auth::routes();

Route::middleware(["auth"])->group(function () {
    Route::get("/dashboard", [HomeController::class, "dashboard"])->name("dashboard");
    Route::get("/search", 'SearchController@search')->name("search");

    Route::post('customers/update-with-data', 'CustomerController@createWithData');
    Route::get('customers/list', 'CustomerController@getList');
    Route::get('customers/get-options', 'CustomerController@getOptions');
    Route::get('customers/{customerId}/with-data', 'CustomerController@getCustomerWithData');
    Route::get('customers/filter', 'CustomerController@getFilters');
    Route::post('subscriptions/delete', 'SubscriptionController@delete');

    Route::get('products/list', 'ProductController@getList');
    Route::get('products/filter', 'ProductController@getFilters');

    Route::get('subscriptions/list', 'SubscriptionController@getList');
    Route::get('subscriptions/filter', 'SubscriptionController@getFilters');

    Route::get('payments/list', 'PaymentController@getList');
    Route::get('payments/filter', 'PaymentController@getFilters');

    Route::get('products/with-prices', 'ProductController@withPrices');

    Route::resources([
        'customers' => 'CustomerController',
        'products' => 'ProductController',
        'subscriptions' => 'SubscriptionController',
        'payments' => 'PaymentController',
    ]);
});

Route::get("/pull", [HomeController::class, "pull"])->name("pull");

Route::get('cloudpayments/{slug}', 'CloudPaymentsController@showWidget')->name('cloudpayments.show_widget');
Route::post('cloudpayments/pay', 'CloudPaymentsController@pay')->name('cloudpayments.pay');
Route::post('cloudpayments/AcsUrl', 'CloudPaymentsController@AcsUrl')->name('cloudpayments.AcsUrl');
