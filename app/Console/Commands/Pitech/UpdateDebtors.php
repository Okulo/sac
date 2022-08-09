<?php

namespace App\Console\Commands\Pitech;

use App\Http\Controllers\CloudPaymentsController;
use App\Http\Controllers\SubscriptionController;
use App\Models\Card;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\CloudPaymentsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class UpdateDebtors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pitech:updatedebtors'; // Date example: 2020-12-31

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для обновления статуса платежей';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @param  Request $request
     * @param  int  $subscriptionId
     * @param int $cardId
     * @return \Illuminate\Http\Response
     * @return int
     */
    public function handle()
    {
        $debtors = Subscription::where('status','debtor')->get();

        foreach ($debtors as $debtor){
            $now = Carbon::now();
            $endedAt = Carbon::parse($debtor->ended_at);

            if($debtor->payment_type == "pitech"){
                $card = Card::where('cp_account_id', $debtor->id)->first();
                if(isset($card) && ($endedAt < $now)){

                    try{
                        $objetoRequest = new \Illuminate\Http\Request();
                        $objetoRequest->setMethod('POST');
                        $objetoRequest->request->add([
                            'customer' => $debtor->customer_id,
                            'subId' => $debtor->id,
                            'product' => $debtor->product->title,
                            'price' => $debtor->price,
                        ]);

                        $subscr = new CloudPaymentsController();
                        $subscr->manualPitechPayment($objetoRequest);

                    } catch (\Throwable $e) {
                        throw new \Exception('Ошибка авто списания стредств с должника по Pitech. ID:  '.$debtor->id, 500);
                    }
                }

            }
        }
    }
}
