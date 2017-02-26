@extends('layouts.main')

@section('title', 'Админка')

@section('content')
    <style>
        img {
            width: 200px;
        }
    </style>
    <div class="col-md-6">{!! $html !!}</div>
    <div class="col-md-6">
        @foreach($data as $key => $value)
            <h5>{{ $key }}</h5>
            <pre>{!! $value !!}</pre>
        @endforeach
    </div>
@endsection
