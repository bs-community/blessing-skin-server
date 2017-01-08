@extends('errors.general')

@section('title', trans('errors.general.title'))

@section('content')
<h1>{{ trans('errors.general.title') }}
@include('setup.wizard.language')
</h1>

<p>{{ trans('errors.exception.code', ['code' => $code]) }}</p>
<p>{!! trans('errors.exception.message', ['msg' => $message]) !!}</p>
@endsection
