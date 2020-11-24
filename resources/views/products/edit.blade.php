@extends('adminlte::page')

@section('title', 'Изменить данные продукта')

@section('content_header')
<h1>Изменить данные продукта</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('products.update', [$product->id]) }}" method="POST">
        {{ csrf_field() }}
        {{ method_field('PATCH') }}
            <div class="form-group row">
                <label for="code" class="col-sm-2 col-form-label">Код</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="code" value="{{ $product->code }}" name="code">
                </div>
            </div>
            <div class="form-group row">
                <label for="title" class="col-sm-2 col-form-label">Заголовок</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="title" value="{{ $product->title }}" name="title">
                </div>
            </div>
            <div class="form-group row">
                <label for="description" class="col-sm-2 col-form-label">Описание</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="description" value="" name="description">{{ $product->description }}</textarea>
                </div>
            </div>
            <div class="form-group row">
                <label for="price" class="col-sm-2 col-form-label">Цена</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="price" value="{{ $product->price }}" name="price">
                </div>
            </div>
            <div class="form-group row">
                <label for="trial_price" class="col-sm-2 col-form-label">Пробная цена</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="trial_price" value="{{ $product->trial_price }}" name="trial_price">
                </div>
            </div>

            <div class="form-group">
                <input type="submit" value="Сохранить" class="btn btn-success" />
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