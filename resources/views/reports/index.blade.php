@extends('adminlte::page')

@section('content')
            <example-component
                prefix-prop="subscriptions"
                create-link-prop="{{ route('subscriptions.create') }}"
            ></example-component>


@stop
