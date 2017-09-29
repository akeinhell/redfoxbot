@extends('layouts.main')

@section('title', 'Настройки бота')

@section('content')
    <div class="container">
        <div id="root"></div>
        <div class="row">
            @include('partials.donate')
        </div>
    </div>
    <script src="/dist/js/index.js"></script>
@endsection

