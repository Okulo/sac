@extends('adminlte::page')

@section('title', 'Изменить данные пользователя')

@section('content_header')
<h1>Перенос абонементов</h1>
    <br>
    Внимане! Всетзь кгт фу абонементы будут перенесены от одного оператора к другому.
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form id="saveUser" action="/users/saveChangeOperator" method="POST">
        {{ csrf_field() }}
        {{ method_field('POST') }}
            <div class="form-group row">
                <label for="name" class="col-sm-2 col-form-label">От оператора</label>

                <div class="col-sm-3">
                    <select id="old_user" class="form-control selectpicker" name="old_user">
                        <option value="">Выберите оператора </option>
                        @foreach($users as $key=>$value)
                            <option value="{{ $key }}" {{ '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>

                </div>

            </div>
            <div class="form-group row">
                <label for="name" class="col-sm-2 col-form-label">Услуга</label>

                <div class="col-sm-3">
                    <select id="product" class="form-control selectpicker" name="product">
                        <option value="">Выберите услугу</option>
                        @foreach($product as $key=>$value)
                            <option value="{{ $key }}" {{ '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>

                </div>

            </div>
            <div class="form-group row">
                <label for="name" class="col-sm-2 col-form-label">К оператору</label>

                    <div class="col-sm-3">
                        <select id="new_user" class="form-control selectpicker" name="new_user">
                            <option value="">Выберите оператора </option>
                        @foreach($users as $key=>$value)
                                <option value="{{ $key }}" {{ '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>

                </div>

            </div>

            <div class="form-group">
                <a href="#" id="save" class="btn btn-success">Сохранить</a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#save').on('click', function(e) {
            if (confirm('Подтверждаете смену оператора? ')) {
                $("#saveUser").submit();
            }
        });
    });
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
    @if(session()->has('success'))
        $(document).Toasts('create', {
            title: 'Успешно.',
            body: '{{ session()->get("success") }}',
            autohide: true,
            delay: 5000
        });
    @endif
</script>
@stop
