<?php

namespace App\Http\Controllers;

use App\Models\CpNotification;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\UserLog;
use App\Services\CloudPaymentsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\String\s;

class ReportController extends Controller
{

    const STATUSES = [
        'waiting' => 'Ожидает',
        'paid' => 'Оплачен',
        'rejected' => 'Отклонен',
        'refused' => 'Отменен',
    ];
    const REASONS = [
        'ExceedsApprovalAmount' => 'Достигнут лимит по сумме операций',
        'InsufficientFunds' => 'Недостаточно средств',
        'TransactionNotPermitted' => 'Транзакция не разрешена',
        'DoNotHonor' => 'Не обслуживать',
        'PickUpCardSpecialConditions' => 'Специальный отказ банка-эмитента',
        'AuthenticationFailed' => 'Ошибка аутентификации',
        'SystemError' => 'Технический сбой эквайера',
        'SuspectedFraud' => 'Предполагаемое мошенничество',
        'RestrictedCard2' => 'Карта заблокирована',
        'NoSuchIssuer' => 'Нет таког эмитента',
        'StolenCard' => 'Карта украдена',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type = 1)
    {
        if ($type == 2) {
            return view('reports.check-pay');
        } elseif ($type == 3) {
            return view('reports.refused');
        } elseif ($type == 4) {
        return view('reports.refusedSubscriptions');
        } elseif ($type == 5) {
            return view('reports.waitingPayment');
        } elseif ($type == 6) {
            return view('reports.waitingPaymentTries');
        } elseif ($type == 7) {
             return view('reports.archivedProducts');
        } elseif ($type == 8) {
            return view('reports.simplePayEnds');
        }elseif ($type == 9) {
            return view('reports.payErrors');
        }
        else {
            return view('reports.index');
        }
    }

    public function checkPayReport($type)
    {
        return view('reports.index');
    }

    public function getList( Request $request)
    {
        if( $request->period == 'week'){
            $startDate = Carbon::now('Asia/Almaty')->subDays(7);
            $endDate = Carbon::now('Asia/Almaty');;
        }
        else{
            $startDate = Carbon::now('Asia/Almaty')->startOfDay();
            $endDate = Carbon::now('Asia/Almaty');
        }

        $cp_data = CpNotification::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at','desc')
            ->get();

       $array = [];
        foreach ($cp_data as $item) {

            if ($item->request['Status'] == 'Declined'){
                $subscription = Subscription::whereId($item->request['AccountId'])->first();


                if(isset($subscription)){
                    $customer = Customer::whereId($subscription['customer_id'])->first();
                   // print_r($subscription->status);
                    array_push($array, [
                        'notific_id' => $item->id,
                        'request' => $item->request,
                        'account_id' => $item->request['AccountId'],
                        'subscription' => $subscription,
                        'card_reason' => $subscription->Reason,
                        'sub_status' => self::STATUSES[$subscription->status],
                        'customer' => $customer,
                        //$subscription['customer_id'],
                    ]);
                }
                else{
                    array_push($array, [
                        'notific_id' => $item->id,
                        'request' => $item->request,
                        'account_id' => $item->request['AccountId'],
                        'subscription' => 'null',
                        'customer' => 'null',
                    ]);
                }

            }
            //$array[] = $item->id;);


        }
        return $array;
      //  print_r($array);


        /* return response()->json([
            'status' => true,
            'data' => 'test'
        ]);
        */
    }

    public function getPayList()
    {
        //$cp_pay = Customer::whereId(15948)->first();

        $cp_note = \DB::table('cp_notifications')
            //->whereJsonContains('request->Status', 'Completed')
            ->where('request->Status', 'Completed')
            ->orderBy('created_at','DESC')
            ->get();

     //  return $cp_pay;

        $notpaid = [];

        foreach ($cp_note as $item) {


            $obj = json_decode($item->request);
            // echo $obj->AccountId.' - '.$obj->TransactionId.'<br>';

            $subscription = \DB::table('subscriptions')
                ->join('customers', 'subscriptions.customer_id', '=', 'customers.id')
                ->where('subscriptions.id', '=' , $obj->AccountId)
                ->where('subscriptions.status','=', 'waiting')
                ->where('subscriptions.payment_type','=', 'cloudpayments')
                ->select('subscriptions.*', 'customers.phone', 'customers.name')
                ->get();

//            $subscription = $subscription = Subscription::whereId($obj->AccountId)
//                ->where('status','=', 'waiting')
//                ->get();

           $result = json_decode($subscription);

            foreach ($result as $pay)
            {
               // echo $pay->subscription_id.' - '. $pay->customer_id.' - '.$pay->amount.' - '.$pay->status.' - '.$pay->paided_at.' - '.$pay->created_at.'<br>';
                array_push($notpaid,$pay);
            }


        }

        return $notpaid;
    }

    public function getRefusedList( Request $request)
    {
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        $subscription = \DB::table('subscriptions')
            ->join('customers', 'subscriptions.customer_id', '=', 'customers.id')
            ->join('reasons', 'subscriptions.reason_id', '=', 'reasons.id')
          //  ->whereDate('subscriptions.updated_at', '>=', $startDate)
          //  ->whereDate('subscriptions.updated_at', '<=', $endDate)
            ->whereBetween('subscriptions.updated_at', [$startDate,$endDate])
            ->where('subscriptions.status', 'refused')
            ->where('subscriptions.payment_type', 'transfer')
            ->select('subscriptions.*', 'customers.phone', 'customers.name','reasons.title')
            ->orderBy('subscriptions.updated_at', 'desc')
            ->get();


        return $subscription;
    }

    public function getRefusedSubscriptionsList( Request $request)
    {

        ($request->startDate != 'Invalid date') ? $startDate = $request->startDate :  $startDate = '2022-04-10 00:00:01';
        ($request->endDate != 'Invalid date') ? $endDate = $request->endDate : $endDate = Carbon::now()->addMonth();

        $subscription = \DB::table('subscriptions')
            ->leftJoin('customers', 'subscriptions.customer_id', '=', 'customers.id')
           ->leftJoin('reasons', 'subscriptions.reason_id', '=', 'reasons.id')
            // ->whereDate('subscriptions.ended_at', '>=', '2022-03-15 00:00:00')
            //  ->whereDate('subscriptions.updated_at', '<=', $endDate)
            ->whereBetween('subscriptions.ended_at', [$startDate, $endDate])
            ->where('subscriptions.status', 'refused')
            ->whereNull('subscriptions.wa_status')
            ->where('subscriptions.payment_type', 'cloudpayments')
            ->select('subscriptions.*', 'customers.phone', 'customers.name','reasons.title')
            ->orderBy('subscriptions.ended_at')
            ->get();

        return $subscription;
    }

    public function getWaitingPay( Request $request)
    {
        $today  = Carbon::now();
        ($request->startDate != 'Invalid date') ? $startDate = $request->startDate :  $startDate = '2022-04-10 00:00:01';
        ($request->endDate != 'Invalid date') ? $endDate = $request->endDate : $endDate = Carbon::now()->addMonth();

        $query = Subscription::whereNull('subscriptions.deleted_at')
            ->leftJoin('customers', 'subscriptions.customer_id', '=', 'customers.id')
            ->leftJoin('reasons', 'subscriptions.reason_id', '=', 'reasons.id')
            ->leftJoin('products', 'subscriptions.product_id', '=', 'products.id')
            ->whereBetween('subscriptions.ended_at', [$startDate, $endDate])
            ->where('subscriptions.status', 'waiting');

              if ($request->product != null) {
                  $query->where('subscriptions.product_id', $request->product );
              }
              if ($request->product == null) {
                  $query->whereIn('subscriptions.product_id', [1,3,9,13,16,20,22,23,24,25]);
              }

              if ($request->tries != 1) {
                $query->where('subscriptions.tries_at','<', $today);
                }
              if ($request->tries == 1) {
                  $query->where('subscriptions.tries_at','>', $today );
                }

            $query->select('subscriptions.*', 'customers.phone', 'customers.name','reasons.title','products.title AS ptitle');

              if ($request->tries != 1) {
                  $query->orderBy('subscriptions.ended_at', 'asc');
              }
              if ($request->tries == 1) {
                  $query->orderBy('subscriptions.tries_at', 'asc');
              }


            $subscriptions = $query->get();
            return $subscriptions;

       // $subscriptions->comments->count();
//        foreach ($subscriptions as $subscription) {
//           echo $subscription->id;
//           echo "<br>";
//
//        }



//        $waitingPayments = \DB::table('subscriptions')
//            ->leftJoin('customers', 'subscriptions.customer_id', '=', 'customers.id')
//            ->leftJoin('reasons', 'subscriptions.reason_id', '=', 'reasons.id')
//            ->leftJoin('products', 'subscriptions.product_id', '=', 'products.id')
//            ->leftJoin('processed_subscription', 'subscriptions.id', '=', 'processed_subscription.subscription_id')
//            ->whereIn('subscriptions.product_id', [1,3,9,13,16,20,22,23])
//            ->whereNull('subscriptions.deleted_at')
//            ->where('subscriptions.status', 'waiting')
//            ->select('subscriptions.*', 'customers.phone', 'customers.name','reasons.title','products.title AS ptitle','processed_subscription.process_status')
//            ->orderBy('subscriptions.updated_at', 'desc')
//            ->get();
//
//
//        return $waitingPayments;



    }

    public function getSubscription( Request $request)
    {
              if(isset($request->id)){
                  $cloudPaymentsService = new CloudPaymentsService();
                  $response = $cloudPaymentsService->getSubscription($request->id);
                  return $response;
              }
              else{
                  return 'Error';
              }

    }

    public function getUserPayments( Request $request)
    {
        $payments = \DB::table('payments')
            ->where('subscription_id', '=' , $request->subId)
            ->where('status', '=' , 'Completed')
            ->select('payments.*')
            ->count();

        return json_decode($payments);
    }

    public function getArchivedProducts( Request $request)
    {
        $products = \DB::table('products')
            ->whereNotNull('archived')
            ->get();

        return json_decode($products);
    }

    public function simplePayEndsList( Request $request)
    {
        $endDate = Carbon::now()->addDays(5);
        $today = Carbon::now();

        $subscriptions = \DB::table('subscriptions')
            ->join('customers', 'subscriptions.customer_id', '=', 'customers.id')
            ->leftJoin('products', 'subscriptions.product_id', '=', 'products.id')
            ->where('subscriptions.ended_at', '<',$endDate)
            ->where('subscriptions.status', 'paid')
            ->whereNull('subscriptions.deleted_at')
            ->where('subscriptions.payment_type', 'transfer');
        if ($request->product){
            $subscriptions->where('subscriptions.product_id', $request->product );
        }

            $subscriptions->select('subscriptions.*', 'customers.phone', 'customers.name','products.title AS ptitle')
            ->orderBy('subscriptions.ended_at', 'ASC')
            ->limit(200);
        $query = $subscriptions->get();
        return $query;
        //return json_decode($subscriptions);
    }

    public function addWaStatus(Request $request){
        $updateWa = \DB::table('subscriptions')
            ->where('id', $request->id)
            ->update(['wa_status' => $request->waStatus]);

        return $updateWa;
    }

    public function getProcessedStatus( Request $request){
        $setStatus = \DB::table('processed_subscription')
            ->where('report_type', $request->type)
            ->get();

        return $setStatus;
    }

    public function getPayErrorList( Request $request){
      // $logs = UserLog::where('type',2)->limit(200)->groupBy('subscription_id')->orderBy('id','desc')->get();
      //  $logs = UserLog::limit(1000)->get();

        if ($request->product){
            $logs = \DB::select('SELECT
                          user_logs.`type`,
                          user_logs.subscription_id,
                          user_logs.user_id,
                          user_logs.id,
                          user_logs.`data`,
                          user_logs.created_at,
                          user_logs.updated_at,
                          user_logs.customer_id,
                          subscriptions.id,
                          subscriptions.customer_id,
                          subscriptions.price,
                          subscriptions.`status`,
                          subscriptions.product_id,
                          customers.name,
                          products.title
                FROM
                  user_logs
                LEFT JOIN subscriptions ON (user_logs.subscription_id = subscriptions.id)
                LEFT JOIN customers ON (subscriptions.customer_id = customers.id)
                     LEFT JOIN products ON (subscriptions.product_id = products.id)
                WHERE user_logs.id IN
                  (SELECT
                    MAX(user_logs.id)
                  FROM
                    user_logs
                  GROUP BY user_logs.subscription_id)
                 AND (user_logs.data LIKE "%rejected%" OR user_logs.data LIKE "%error%")
                 AND subscriptions.product_id = '.$request->product.'
                ORDER BY user_logs.id DESC
                LIMIT 500');
        }
        else{
            $logs = \DB::select('SELECT
                          user_logs.`type`,
                          user_logs.subscription_id,
                          user_logs.user_id,
                          user_logs.id,
                          user_logs.`data`,
                          user_logs.created_at,
                          user_logs.updated_at,
                          user_logs.customer_id,
                          subscriptions.id,
                          subscriptions.customer_id,
                          subscriptions.price,
                          subscriptions.`status`,
                          subscriptions.product_id,
                          customers.name,
                          products.title
                FROM
                  user_logs
                LEFT JOIN subscriptions ON (user_logs.subscription_id = subscriptions.id)
                LEFT JOIN customers ON (subscriptions.customer_id = customers.id)
                     LEFT JOIN products ON (subscriptions.product_id = products.id)
                WHERE user_logs.id IN
                  (SELECT
                    MAX(user_logs.id)
                  FROM
                    user_logs
                  GROUP BY user_logs.subscription_id)
                 AND (user_logs.data LIKE "%rejected%" OR user_logs.data LIKE "%error%")
                ORDER BY user_logs.id DESC
                LIMIT 500');
        }

        return $logs;
    }

    public function setProcessedStatus( Request $request){
        $setStatus = \DB::table('processed_subscription')
            ->updateOrInsert(
            ['subscription_id' =>   request('subId'),'report_type' => request('report_type')],
            ['process_status' => request('status')]
        );

        return $setStatus;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
