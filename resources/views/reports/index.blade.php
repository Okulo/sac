@extends('adminlte::page')

@section('content')
<div class="card">
    <div class="card-body">


        <div data-v-754b2df6="" class="mb-2">
            <div data-v-754b2df6="" class="input-group"><div data-v-754b2df6="" style="flex: 1 1 auto; margin-left: 7px; margin-right: 10px;"><input data-v-754b2df6="" type="text" name="customer_name_or_phone" placeholder="Здесь будет дата " aria-label="search" aria-describedby="search-icon" class="form-control"></div>
                <button data-v-754b2df6="" type="button" class="btn btn-success btn-sm">Найти</button>

                </div>
            </div>
        </div>

    @section('content')
        <div class="table-responsive bg-white">
            <example-component
                prefix-prop="subscriptions"
                create-link-prop="{{ route('subscriptions.create') }}"
            ></example-component>
        </div>
    @stop
    </div>
</div>
@stop
