@extends('errors.general')

@section('title', '404 Not Found')

@section('content')

<h1>404 Not Found</h1>

<p>{{ trans('errors.exception.message', ['msg' => $exception->getMessage() ?: trans('errors.http.msg-404')]) }}</p>
@endsection
