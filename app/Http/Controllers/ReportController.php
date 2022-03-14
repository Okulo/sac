<?php

namespace App\Http\Controllers;

use App\Models\CpNotification;
use App\Models\Customer;
use App\Models\Subscription;
use App\Services\CloudPaymentsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('reports.index');
    }

    public function getList( Request $request)
    {
        if( $request->period == 'week'){
            $startDate = 1;
            $endDate = 2;
            dd(Carbon::now()->startOfDay());
            dd(Carbon::now()->startOfDay());
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

                    array_push($array, [
                        'notific_id' => $item->id,
                        'request' => $item->request,
                        'account_id' => $item->request['AccountId'],
                        'subscription' => $subscription,
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
