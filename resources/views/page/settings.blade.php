@extends('layouts.main')

@section('title', 'Настройки бота')

@section('content')
    <div class="container">
        <div id="root"></div>
        <div class="row">
            @include('partials.donate')
        </div>
    </div>
@endsection

@section('footer')
    @asset(index, js)
@endsection