<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\StatisticsModel;
use App\Models\Subscription;
use App\Models\UsersBonuses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class UsersBonusesController extends Controller
{
    private function getPeriods($periodType, $from, $to)
    {
        $start = new \DateTime($from);
        $end   = new \DateTime($to);
        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($start, $interval, $end);
        $data = [];
        foreach ($period as $dt)
        {
            if ($periodType == 'week') {
                $day = (int) Carbon::parse($dt)->endOfWeek()->startOfDay()->valueOf();
                if (! in_array($day, $data)) {
                    $data[] = $day;
                }
            } else if ($periodType == 'month') {
                $day = (int) Carbon::parse($dt)->endOfMonth()->startOfDay()->valueOf();
                if (! in_array($day, $data)) {
                    $data[] = $day;
                }
            }
        }
        return $data;
    }

    public function show(Request $request)
    {
        access(['can-head', 'can-host', 'con-operator']);
        if (!$request->has('from') && !$request->has('to')) {
            $data = [
                "period" => $request->input('period') ?? 'week',
                "productId" => $request->input('productId') ?? Product::first()->id ?? null,
                "from" => $request->input('from') ?? Carbon::now()->subMonths(3)->format('Y-m-d'),
                "to" => $request->input('to') ?? Carbon::now()->format('Y-m-d'),
            ];
            return redirect()->route('users_bonuses.show', $data);
        }

        $products = Product::get()->pluck('title', 'id');

        $request->validate([
            "from" => "required|date_format:Y-m-d",
            "to" => "required|date_format:Y-m-d",
            "productId" => "required",
            "period" => "required",
        ]);

        $period = $request->input('period');
        $from = Carbon::createFromFormat('Y-m-d', $request->input('from'), 'Asia/Almaty')->startOfDay()->setTimezone('Asia/Almaty');
        $to = Carbon::createFromFormat('Y-m-d', $request->input('to'), 'Asia/Almaty')->endOfDay()->setTimezone('Asia/Almaty');
        $categories = $this->getPeriods($request->get('period'), $from, $to);
        $productId = $request->input('productId');
        $usersBonuses = UsersBonuses::join('bonuses', 'bonuses.id', '=', 'users_bonuses.bonus_id')
            ->join('payment_types', 'payment_types.id', '=', 'bonuses.payment_type_id')
            ->select(
                'users_bonuses.*',
                'bonuses.type',
                'bonuses.amount as bonuses_amount',
                'payment_types.payment_type',
                \DB::raw('(users_bonuses.amount * bonuses.amount) as total_bonus')
            )
            ->where('users_bonuses.product_id', $productId)
            ->where('users_bonuses.date_type', $period)
            ->get()->groupBy(function ($item, $key) {
                return $item->payment_type . '-' . $item->type;
            })->toArray();

        $usersBonusesGroup = UsersBonuses::join('bonuses', 'bonuses.id', '=', 'users_bonuses.bonus_id')
            ->join('payment_types', 'payment_types.id', '=', 'bonuses.payment_type_id')
            ->select(
                \DB::raw("SUM(users_bonuses.amount * bonuses.amount) as total_bonus"),
                'users_bonuses.unix_date'
            )
            ->where('users_bonuses.product_id', $productId)
            ->where('users_bonuses.date_type', $period)
            ->groupBy('users_bonuses.unix_date')
            ->get();

        $usersBonusesForChart = $usersBonusesGroup->pluck('total_bonus', 'unix_date')->toArray();

        $chart = [
            'type' => 'highchart',
            'chart' => [
                'type' => 'area',
            ],
            "title" => ["text" => 'Сумма бонусов за неделю'],
            'xAxis' => [
                'type' => 'datetime',
            ],
            "series" => [
                [
                    'editable' => false,
                    "name" => "Сумма",
                    "data" => array_values(collect($categories)->map(function ($category, $key) use ($usersBonusesForChart) {
                        return ['name' => Carbon::parse((int) $category / 1000)->setTimezone('Asia/Almaty')->isoFormat('DD MMM, YY'), 'x' => $category, 'y' => (int) ($usersBonusesForChart[$category] ?? 0)];
                    })->toArray()),
                    "color" => "#c2de80",
                ],
            ],
            'plotOptions' => [
                'area' => [
                    'fillOpacity' => 0.5,
                    'dataLabels' => [
                        'enabled' => true,
                    ],
                ],
            ],
        ];

        return view('users-bonuses.show', compact('products', 'chart', 'usersBonuses'));
    }
}
