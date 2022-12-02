<?php

namespace App\Http\Controllers;

use App\Models\CpNotification;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserLog;
use App\Services\CloudPaymentsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        elseif ($type == 10) {
            return view('reports.debtors');
        }
        elseif ($type == 11) {
            return view('reports.operators-bonuses');
        }
        elseif ($type == 12) {
            return view('reports.sales');
        }
        elseif ($type == 13) {
            return view('reports.waitingDelay');
        }
//        elseif ($type == 11) {
//            if(Auth::user()->role->code != 'operator'){
//                return view('reports.operators-bonuses');
//            }
//            else {
//                echo "Отказано в доступе";
//            }
//
//
//        }
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
        ($request->startDate != 'Invalid date') ? $startDate = $request->startDate :  $startDate = '2022-01-01 00:00:01';
        ($request->endDate != 'Invalid date') ? $endDate = $request->endDate : $endDate = Carbon::now()->addMonth();

        $query = Subscription::leftJoin('customers', 'subscriptions.customer_id', '=', 'customers.id')
            ->leftJoin('reasons', 'subscriptions.reason_id', '=', 'reasons.id')
            ->leftJoin('products', 'subscriptions.product_id', '=', 'products.id')
            ->leftJoin('users', 'subscriptions.user_id', '=', 'users.id')
            ->whereBetween('subscriptions.ended_at', [$startDate, $endDate]);


            if ($request->product != null) {
                $query->where('subscriptions.product_id', $request->product );
            }
            if ($request->product == null) {
                $query->where('category', 1);
            }
            if ($request->userId != null) {
                $query->where('subscriptions.user_id', $request->userId );
            }

            if ($request->tries != 1) {
                $query->where('subscriptions.status', 'waiting');
            }
            if ($request->tries == 1) {
                $query->where('subscriptions.status', 'tries');
            }

           $query->select('subscriptions.*', 'customers.phone', 'customers.name','reasons.title','products.title AS ptitle','users.name AS user_name');

              if ($request->tries != 1) {
                  $query->orderBy('subscriptions.ended_at', 'asc');
              }
              if ($request->tries == 1) {
                  $query->orderBy('subscriptions.tries_at', 'asc');
              }

            $subscriptions = $query->limit(300)->get();

            $setStatus = \DB::table('processed_subscription')
            ->where('report_type', $request->reportType)
            ->get();




//            foreach ($setStatus as $item){
//                $status[] = $item;
//            }


              foreach ($subscriptions as $subscription){
                  $payments = Payment::where('subscription_id', $subscription->id)->get();
                  if($payments->count()){
                      $subscription->payments = $payments;
                  }
                  else{
                      $subscription->payments = '';
                  }

                  foreach ($setStatus as $item){
                    //  $status[] = $item;
                      if ($subscription->id == $item->subscription_id) {
        //                  $subscription->s = $item->status;
//                        $subscription->report_type = $item->report_type;
                        $subscription->process_status = $item->process_status;
                         // echo $item->subscription_id;
                      }
                  }

              }

           return $subscriptions;

    }

    public function getDelaylist( Request $request){
        $today  = Carbon::now();
        $tommorow =  Carbon::now()->subDay();
        ($request->startDate != 'Invalid date') ? $startDate = $request->startDate :  $startDate = '2020-01-01 00:00:01';
        ($request->endDate != 'Invalid date') ? $endDate = $request->endDate : $endDate = Carbon::now()->addMonth();

        $query = Subscription::leftJoin('customers', 'subscriptions.customer_id', '=', 'customers.id')
            ->leftJoin('reasons', 'subscriptions.reason_id', '=', 'reasons.id')
            ->leftJoin('products', 'subscriptions.product_id', '=', 'products.id')
            ->leftJoin('users', 'subscriptions.user_id', '=', 'users.id')
            ->whereBetween('subscriptions.ended_at', [$startDate, $endDate]);


        if ($request->product != null) {
            $query->where('subscriptions.product_id', $request->product );
        }
        if ($request->product == null) {
            $query->where('category', 1);
        }
        if ($request->userId != null) {
            $query->where('subscriptions.user_id', $request->userId );
        }
        if ($request->delay == 1) {
            $query->where('subscriptions.tries_at', '<', $tommorow);
        }
        if ($request->delay != 1) {
            $query->where('subscriptions.tries_at', '>', $today);
        }


            $query->where('subscriptions.status', 'tries');

        $query->select('subscriptions.*', 'customers.phone', 'customers.name','reasons.title','products.title AS ptitle','users.name AS user_name');

        $query->orderBy('subscriptions.tries_at', 'asc');


        $subscriptions = $query->limit(300)->get();

        $setStatus = \DB::table('processed_subscription')
            ->where('report_type', $request->reportType)
            ->get();

        foreach ($subscriptions as $subscription){

            foreach ($setStatus as $item){
                //  $status[] = $item;
                if ($subscription->id == $item->subscription_id) {
                    //                  $subscription->s = $item->status;
//                        $subscription->report_type = $item->report_type;
                    $subscription->process_status = $item->process_status;
                    // echo $item->subscription_id;
                }
            }

        }

        return $subscriptions;

    }

    public function getDebtorsList( Request $request)
    {
        $today  = Carbon::now();
        ($request->startDate != 'Invalid date') ? $startDate = $request->startDate :  $startDate = '2022-01-01 00:00:01';
        ($request->endDate != 'Invalid date') ? $endDate = $request->endDate : $endDate = Carbon::now()->addMonth();

        $query = Subscription::leftJoin('customers', 'subscriptions.customer_id', '=', 'customers.id')
            ->leftJoin('products', 'subscriptions.product_id', '=', 'products.id')
            ->where('subscriptions.status', 'debtor');

        $query->select('subscriptions.*', 'customers.phone', 'customers.name','products.title AS ptitle');

        $subscriptions = $query->get();
        return $subscriptions;

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
        if ($request->userId != null) {
            $subscriptions->where('subscriptions.user_id', $request->userId );
        }
//        else{
//            $subscriptions->whereIn('subscriptions.product_id',[1,3,27,26,25,22,23,24]);
//        }

        $subscriptions->select('subscriptions.*', 'customers.phone', 'customers.name','products.title AS ptitle')
            ->orderBy('subscriptions.ended_at', 'ASC')
            ->limit(200);
        $query = $subscriptions->get();

        $setStatus = \DB::table('processed_subscription')
            ->where('report_type', 8)
            ->get();

        foreach ($query as $subscription){

            foreach ($setStatus as $item){

                if ($subscription->id == $item->subscription_id) {
                    $subscription->process_status = $item->process_status;
                }
            }

        }

        return $query;
        //return json_decode($subscriptions);
    }

    public function addWaStatus(Request $request){
        $updateWa = \DB::table('subscriptions')
            ->where('id', $request->id)
            ->update(['wa_status' => $request->waStatus]);

        return $updateWa;
    }

    public function saveStatus(Request $request)
    {
        $updateStatus = \DB::table('subscriptions')
            ->where('id', $request->subId)
            ->update(['status' => $request->status]);

        return $updateStatus;
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
        $today = Carbon::now();

//        $subscriptions = \DB::table('payments')
//            ->leftJoin('customers', 'payments.customer_id', '=', 'customers.id')
//            ->leftJoin('products', 'payments.product_id', '=', 'products.id')
//            ->leftJoin('subscriptions', 'payments.subscription_id', '=', 'subscriptions.id')
//            ->whereIn('payments.id',function ($query) {
//                $query->select('payments.id', \DB::raw('MAX(payments.id)'))->groupBy('payments.customer_id');
//
//            })
//            ->where('payments.status', 'Declined')
//            ->where('subscriptions.status', '!=', 'refused')
//            ->where('subscriptions.status', '!=', 'debtor')
//            ->whereNull('subscriptions.deleted_at')
//            ->where('subscriptions.ended_at', '<', $today)
//            ->where('payments.product_id', 3);
//        $subscriptions->select('payments.id','payments.subscription_id','payments.customer_id','payments.type','payments.status','payments.amount',
//            'payments.data','payments.paided_at','payments.created_at','customers.name','customers.phone','products.title','subscriptions.id as sub_id',
//            'subscriptions.status','subscriptions.ended_at','subscriptions.payment_type')
//            ->orderBy('payments.id', 'DESC')
//            ->limit(700);
//        $query = $subscriptions->get();
//        return $query;

        if ($request->product){

            $procesStatus = \DB::table('processed_subscription')
                ->where('report_type', 9)
                ->get();

            $logs = \DB::select('SELECT
                            payments.id,
                            payments.subscription_id,
                            payments.customer_id,
                            payments.type,
                            payments.status,
                            payments.amount,
                            payments.data,
                            payments.paided_at,
                            payments.created_at,
                             customers.name,
                             customers.phone,
                             products.title,
                             `subscriptions`.id as sub_id,
                             `subscriptions`.`status`,
                              `subscriptions`.`ended_at`,
                             `subscriptions`.`payment_type`
                        FROM `payments`
                        LEFT JOIN customers ON (payments.customer_id = customers.id)
                        LEFT JOIN products ON (payments.product_id = products.id)
                         LEFT JOIN `subscriptions` ON (payments.`subscription_id` = `subscriptions`.id)
                        WHERE payments.id IN
                          (SELECT
                            MAX(payments.id)
                          FROM
                            payments
                          GROUP BY payments.customer_id)
                          AND payments.status = \'Declined\'
                          AND `subscriptions`.`deleted_at` is null
                          AND `subscriptions`.`status` != "refused"
                          AND `subscriptions`.`status` != "debtor"
                          AND `subscriptions`.`ended_at` < "'.$today.'"
                          AND payments.product_id = '.$request->product.'
                          ORDER BY payments.id DESC
                          LIMIT 700');
        }
        else{
            $procesStatus = \DB::table('processed_subscription')
                ->where('report_type', 9)
                ->get();

            $logs = \DB::select('SELECT
                            payments.id,
                            payments.subscription_id,
                            payments.customer_id,
                            payments.type,
                            payments.status,
                            payments.amount,
                            payments.data,
                            payments.paided_at,
                            payments.created_at,
                             customers.name,
                             customers.phone,
                             products.title,
                             `subscriptions`.id as sub_id,
                             `subscriptions`.`status`,
                             `subscriptions`.`ended_at`,
                             `subscriptions`.`payment_type`
                        FROM `payments`
                        LEFT JOIN customers ON (payments.customer_id = customers.id)
                        LEFT JOIN products ON (payments.product_id = products.id)
                         LEFT JOIN `subscriptions` ON (payments.`subscription_id` = `subscriptions`.id)
                        WHERE payments.id IN
                          (SELECT
                            MAX(payments.id)
                          FROM
                            payments
                          GROUP BY payments.customer_id)
                          AND payments.status = \'Declined\'
                          AND `subscriptions`.`deleted_at` is null
                          AND `subscriptions`.`ended_at` < "'.$today.'"
                          AND `subscriptions`.`status` != "refused"
                          AND `subscriptions`.`status` != "debtor"
                          ORDER BY payments.id DESC
                          LIMIT 700');
        }

        foreach ($logs as $subscription){
            foreach ($procesStatus as $item){
                //  $status[] = $item;
                if ($subscription->id == $item->subscription_id) {
                    //                  $subscription->s = $item->status;
//                        $subscription->report_type = $item->report_type;
                    $subscription->process_status = $item->process_status;
                    // echo $item->subscription_id;
                }
            }

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

    public function getUserBonus( Request $request){

        $startDate = $request->startDate;
        $endDate =  $request->endDate;

      //  if(!isset($request->userId)){
            $bonuses = \DB::select("SELECT  payments.user_id,
                                        users.name,
                                        SUM(product_bonuses.amount) as summa
                                FROM payments
                                    INNER JOIN users ON (payments.user_id = users.id)
                                    INNER JOIN product_bonuses ON (payments.product_bonus_id = product_bonuses.id)
                                WHERE payments.`status` = 'Completed'
                                AND payments.paided_at
                                BETWEEN '".$startDate."'
                                AND '".$endDate."'
                                GROUP BY payments.user_id");
//        }
//        else{
//            $bonuses = \DB::select("SELECT
//                  payments.`status`,
//                  payments.product_id,
//                  payments.user_id,
//                  users.name,
//                  users.id,
//                  product_bonuses.amount,
//                  payments.paided_at,
//                  product_bonuses.product_id,
//                  users.account,
//                  payments.quantity,
//                  payments.`type`,
//                  payments.customer_id,
//                  payments.subscription_id,
//                  payments.amount,
//                  payments.product_bonus_id,
//                  products.title
//                FROM
//                  payments
//                  INNER JOIN users ON (payments.user_id = users.id)
//                  INNER JOIN product_bonuses ON (payments.product_bonus_id = product_bonuses.id)
//                  INNER JOIN products ON (payments.product_id = products.id)
//                WHERE
//                  payments.`status` = 'Completed' AND
//                  payments.user_id = '".$request->userId."'
//                ORDER BY
//                  payments.quantity");
//            }

        return $bonuses;
    }

    public function operatorBonusDetail($id){

        $user = User::where('id', $id)->get()->first();

        return view('reports.operator-detail', [
            'id' => $id,
            'name' => $user->name
        ]);
    }

    public function getAllSubscriptions(Request $request){
         $subscriptions = Subscription::where('user_id', $request->userId)->where('status','paid')->get();
         return $subscriptions;
    }

    public function myBonuses(){
        $user = Auth::id();
        print_r(Auth::user()->getRole());
        return view('reports.operator-detail', [
            'id' => $user,
            'name' => ''
        ]);
    }

    public function getOperatorSumm (Request $request){

        $startDate = $request->startDate;
        $endDate =  $request->endDate;
        $userId = $request->userId;

        $bonuses = \DB::select("SELECT
                      sum(product_bonuses.amount) as summa
                    FROM
                      payments
                      left JOIN product_bonuses ON (payments.product_bonus_id = product_bonuses.id)
                    WHERE
                      payments.`status` = 'Completed' AND
                      payments.user_id = ".$userId." and
                      payments.paided_at BETWEEN '".$startDate."' AND '".$endDate."'");
        return $bonuses;
    }

    public function getSubscriptions (Request $request){

        $startDate = $request->startDate;
        $endDate =  $request->endDate;
        $userId = $request->userId;

        if($request->count == 'subs'){
            $bonuses = \DB::select("SELECT
                    count(*) as count
                    FROM
                      payments
                    WHERE
                      payments.`status` = 'Completed' AND
                      payments.user_id = ".$userId." AND
                      payments.paided_at BETWEEN '".$startDate."' AND '".$endDate."'
                      AND
                      (payments.`type` = 'cloudpayment' OR   payments.`type` = 'pitech')");
        } else {
            $bonuses = \DB::select("SELECT
                    count(*) as count
                    FROM
                      payments
                    WHERE
                      payments.`status` = 'Completed' AND
                      payments.user_id = ".$userId." AND
                      payments.paided_at BETWEEN '".$startDate."' AND '".$endDate."'
                      AND
                      (payments.`type` = 'transfer')");
        }



        return $bonuses;
    }

//    public function getUserBonus(){
//        $bonuses = \DB::select('SELECT
//  product_bonuses.amount as bonus_amount,
//  product_bonuses.payment_type_id,
//  product_bonuses.`type` AS bonus_type,
//  payments.subscription_id,
//  payments.amount,
//  payments.`status`,
//  payments.`type` AS payment_type,
//  payments.paided_at,
//  users.account,
//  users.name,
//  products.title,
//  products.code,
//  payments.product_id
//FROM
//  payments
//  LEFT JOIN product_bonuses ON (payments.product_bonus_id = product_bonuses.id)
//  INNER JOIN users ON (payments.user_id = users.id)
//  LEFT JOIN products ON (payments.product_id = products.id)
//WHERE
//      payments.paided_at BETWEEN \'2022-07-17 00:00:00\' AND \'2022-08-17 00:00:00\'
//  AND payments.`status` = \'Completed\'
//ORDER BY `payments`.`paided_at`  ASC');
//
//        return $bonuses;
//    }

    public function getSales( Request $request)
    {

        ($request->startDate != 'Invalid date') ? $startDate = $request->startDate :  $startDate = Carbon::now()->subMonth();;
        ($request->endDate != 'Invalid date') ? $endDate = $request->endDate : $endDate = Carbon::now();

        $subscription = \DB::select("SELECT users.`name`,payments.`user_id`,payments.`type`, SUM(amount) as summa
                                        FROM payments
                                        LEFT JOIN users ON (payments.user_id = users.id)
                                        WHERE payments.user_id > 0
                                        AND `type` != 'frozen'
                                        AND users.is_active = 1
                                            AND payments.paided_at
                                            BETWEEN '".$startDate."'
                                            AND '".$endDate."'
                                        GROUP BY `type`, `user_id`
                                        ORDER BY user_id
                                ");


//        $subscription = \DB::table('payments')
//            ->leftJoin('products', 'payments.product_id', '=', 'products.id')
//            ->leftJoin('users', 'payments.user_id', '=', 'users.id')
//            // ->whereDate('subscriptions.ended_at', '>=', '2022-03-15 00:00:00')
//            //  ->whereDate('subscriptions.updated_at', '<=', $endDate)
//            ->whereBetween('payments.paided_at', [$startDate, $endDate])
//            ->where('payments.status', 'Completed')
//            ->select('payments.*', 'users.name', 'products.title')
//            ->get();

        return $subscription;
    }

}
