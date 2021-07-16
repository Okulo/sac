@extends('adminlte::page')

@section('content')
@php
$data = [
    'from' => request()->get('from') ?? null,
    'to' => request()->get('to') ?? null,
    'period' => request()->get('period') ?? null,
    'currentPoint' => request()->get('currentPoint') ?? null,
    'lastPoint' => request()->get('lastPoint') ?? null,
];

@endphp
<manager-bonuses-component 
    :products-prop="{{ json_encode($products) }}"
    :periods-prop="{{ json_encode(\App\Models\Bonus::PERIODS) }}"
    :data-prop="{{ json_encode($data) }}"
    :chart-prop="{{ json_encode($chart) }}"
    :manager-bonuses-group-by-products-prop="{{ json_encode($managerBonusesGroupByProducts) }}"
></manager-bonuses-component>
@stop