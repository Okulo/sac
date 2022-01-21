@extends('adminlte::page')

@section('content')
@php
$data = [
'from' => request()->get('from') ?? null,
'to' => request()->get('to') ?? null,
'productId' => request()->get('productId') ?? null,
'period' => request()->get('period') ?? null,
];
@endphp
<notific-component
></notific-component>
@stop
