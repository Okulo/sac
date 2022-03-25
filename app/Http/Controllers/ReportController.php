<?php

namespace App\Http\Controllers;

use App\Models\CpNotification;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\CloudPaymentsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        } else {
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
        if( $request->period == 'week'){
            $startDate = Carbon::now('Asia/Almaty')->subDays(7);
            $endDate = Carbon::now('Asia/Almaty');;
        }
        else{
            $startDate = Carbon::now('Asia/Almaty')->subMonth();
            $endDate = Carbon::now('Asia/Almaty');
        }

        $subscription = \DB::table('subscriptions')
            ->join('customers', 'subscriptions.customer_id', '=', 'customers.id')
            ->join('reasons', 'subscriptions.reason_id', '=', 'reasons.id')
            ->whereDate('subscriptions.started_at', '>=', $startDate)
            ->whereDate('subscriptions.started_at', '<=', $endDate)
            ->where('subscriptions.status', 'refused')
            ->where('subscriptions.payment_type', 'transfer')
            ->select('subscriptions.*', 'customers.phone', 'customers.name','reasons.title')
            ->orderBy('subscriptions.started_at', 'desc')
            ->get();


        return $subscription;
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
