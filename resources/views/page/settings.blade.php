@extends('layouts.main')

@section('title', 'Настройки бота')
@section('header_block')
    {!! webpack_asset('style', 'index.css') !!}
@endsection


@section('content')
    <div class="container">
        <div id="root"></div>
        <div class="row">
            @include('partials.donate')
        </div>
    </div>
    {!! webpack_asset('script', 'index.js') !!}
@endsection

