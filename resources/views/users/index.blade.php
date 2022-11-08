@extends('adminlte::page')

@section('title', 'Пользователи')

@section('content')
<div class="table-responsive bg-white">
    <div class="col-2 float-right btn btn-outline-info" style="margin:10px 15px"><a href="/users/changeSubscriptions/">Перенос абонементов</a> </div>
    <index-component
        prefix-prop="users"
        create-link-prop="{{ route('users.create') }}"
    ></index-component>
</div>
@stop

@section('css')
@stop

@section('js')
<script>
    @if(session()->has('success'))
        $(document).Toasts('create', {
            title: 'Успешно.',
            body: '{{ session()->get("success") }}',
            autohide: true,
            delay: 5000
        });
    @endif
    @if(session()->has('error'))
    $(document).Toasts('create', {
        title: 'Ошибка!.',
        class: 'toast-warning',
        body: '{{ session()->get("error") }}',
        autohide: false,
        delay: 1000
    });
    @endif
</script>
@stop
