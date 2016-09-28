@extends('errors.general')

@section('title', trans('errors.general.title'))

@section('content')
<h1>{{ $level.': '.trans('errors.exception.title') }}</h1>

<p>{{ trans('errors.exception.message', ['msg' => $message]) }}</p>

<p>{!! trans('errors.exception.location', ['location' => "<b>$file: $line</b>"]) !!}</p>

@endsection
