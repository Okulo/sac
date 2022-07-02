@extends('adminlte::page')

@section('title', 'Создать продукт')

@section('content_header')
<h1>Создать продукт</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
            <div class="form-group row">
                <label for="code" class="col-sm-2 col-form-label">Код</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="code" value="{{ old('code') }}" name="code">
                </div>
            </div>
            <div class="form-group row">
                <label for="title" class="col-sm-2 col-form-label">Заголовок</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="title" value="{{ old('title') }}" name="title">
                </div>
            </div>
            <div class="form-group row">
                <label for="description" class="col-sm-2 col-form-label">Описание</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="form-group row">
                <label for="prices" class="col-sm-2 col-form-label">Цена по акции</label>
                <div class="col-sm-10">
                    <product-price-component :prices-prop="{{ json_encode(old('prices') ?? []) }}"></product-price-component>
                </div>
            </div>
            <div class="form-group row">
                <label for="title" class="col-sm-2 col-form-label">Цена после акции</label>
                <div class="col-sm-2">
                    <input type="number" class="form-control" id="next-price" value="" name="next-price">
                </div>
            </div>
            <div class="form-group row">
                <label for="title" class="col-sm-2 col-form-label">Сумма блокировки</label>
                <div class="col-sm-2">
                    <input type="number" class="form-control" id="block_amount" value="" name="block_amount">
                </div>
            </div>
            <div class="form-group row">
                <label for="title" class="col-sm-2 col-form-label">Триал период ( дней )</label>
                <div class="col-sm-2">
                    <input type="number" class="form-control" id="trial_period" value="" name="trial_period">
                </div>
            </div>
         <!--   <div class="form-group row">
                <label  class="col-sm-2">Включится после</label>
            <select name="period" class="custom-select col-sm-1 " >
                <option value="1">1</option>
                <option value="2" selected>2</option>
                <option value="3">3</option>
            </select>
                <div> &nbsp; &nbsp; <b>платежа </b></div>
            </div> -->

            <hr>
            <div class="form-group row">
                <label for="prices" class="col-sm-2 col-form-label">Доступные типы оплат</label>
                <div class="col-sm-10">
                    <product-payment-type-component :product-payment-types-prop="{{ json_encode(old('paymentTypes') ?? []) }}" :payment-types-prop="{{ json_encode($paymentTypes) }}"></product-payment-type-component>
                </div>
            </div>
            {{--<hr>
            <div class="form-group row">
                <label for="prices" class="col-sm-2 col-form-label">Операторы услуги</label>
                <div class="col-sm-10">
                    <product-users-component :product-users-prop="{{ json_encode(old('productUsers') ?? []) }}" :users-prop="{{ json_encode($users) }}"></product-users-component>
                </div>
            </div>--}}
            <hr>
            <div class="form-group row">
                <label for="prices" class="col-sm-2 col-form-label">Команда продукта</label>
                <div class="col-sm-10">
                    <product-teams-component :product-teams-prop="{{ json_encode(old('productTeams') ?? []) }}" :teams-prop="{{ json_encode($teams) }}"></product-teams-component>
                </div>
            </div>
            <hr>
            <div class="form-group row">
                <label for="prices" class="col-sm-2 col-form-label">Причины отказов</label>
                <div class="col-sm-10">
                    <product-reasons-component :reasons-prop="{{ json_encode(old('reasons') ?? []) }}"></product-reasons-component>
                </div>
            </div>
            <hr>
            <div class="form-group row">
                <label for="title" class="col-sm-2 col-form-label">Имя мастера</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="title" value="{{ old('data.name') ?? 'Екатерина Скурихина' }}" name="data[name]">
                </div>
            </div>

            <div class="form-group row">
                <label for="title" class="col-sm-2 col-form-label">Описание мастера</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="title" value="{{ old('data.position') }}" name="data[position]">
                </div>
            </div>

            <div class="form-group row">
                <label for="title" class="col-sm-2 col-form-label">Заголовок телефона</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="title" value="{{ old('data.phone_title') ?? 'Номер для связи' }}" name="data[phone_title]">
                </div>
            </div>

            <div class="form-group row">
                <label for="title" class="col-sm-2 col-form-label">Телефон</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="title" value="{{ old('data.phone') }}" name="data[phone]">
                </div>
            </div>

            <div class="form-group row">
                <label for="title" class="col-sm-2 col-form-label">Заголовок Instagram-а</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="title" value="{{ old('data.instagram_title') ?? 'Больше полезных видео у меня в инстаграм' }}" name="data[instagram_title]">
                </div>
            </div>

            <div class="form-group row">
                <label for="title" class="col-sm-2 col-form-label">Instagram (ссылка)</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="title" value="{{ old('data.instagram') ?? 'strela_academy' }}" name="data[instagram]">
                </div>
            </div>

            <div class="form-group row">
                <label for="period" class="col-sm-2 col-form-label">Добавить картинку</label>
                <div class="col-sm-10">
                    <div style="max-width: 300px">
                        <upload-file name-prop="data[image]" value-prop="{{ old('data.image') }}"></upload-file>
                    </div>
                </div>
            </div>

            <hr>

            <div class="form-group row">
                <label for="prices" class="col-sm-2 col-form-label">Связанные продукты для рекламы в thank you page</label>
                <div class="col-sm-10">
                    <product-additionals-component :product-additionals-prop="{{ json_encode(old('productAdditionals')) }}" :additionals-prop="{{ json_encode($additionals) }}"></product-additionals-component>
                </div>
            </div>

            <div class="form-group">
                <input type="submit" value="Добавить" class="btn btn-success" />
            </div>
        </form>
        <a href="{{ route('products.index') }}">К списку</a>
    </div>
</div>
@stop

@section('css')
@stop

@section('js')
<script>
    @if($errors->any())
        @foreach($errors->all() as $key => $error)
        $(document).Toasts('create', {
            title: 'Ошибка.',
            body: '{{ $error }}',
            autohide: true,
            delay: 5000
        });
        @endforeach
    @endif
</script>
@stop
