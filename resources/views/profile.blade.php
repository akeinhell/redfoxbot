@extends('layouts.main')

@section('title')
	Профиль
@endsection

@section('content')
	Привет, {{Auth::user()->first_name}}
@endsection
