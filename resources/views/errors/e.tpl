@extends('errors.general')

@section('title', trans('errors.general.title'))

@section('content')
<h1>{{ trans('errors.error.title') }}</h1>

<p>{{ trans('error.exception.code', ['code' => $code]) }}</p>
<p>{!! trans('error.exception.message', ['message' => $message]) !!}</p>
@endsection
