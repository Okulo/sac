@extends('adminlte::page')

@section('content')
            <check-pay
                prefix-prop="subscriptions"
                create-link-prop="{{ route('subscriptions.create') }}"
            ></check-pay>
@stop
