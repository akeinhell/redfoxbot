@extends('layouts.main')

@section('title')
    Главная страница
@endsection

@section('content')
    @foreach($posts as $post)
        @include('layouts.article', $post)
    @endforeach
@endsection


<!--

-->