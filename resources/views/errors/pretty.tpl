@extends('errors.general')

@section('title', trans('errors.general.title'))

@section('content')
<h1>@lang('errors.general.title')
@include('setup.wizard.language')
</h1>

<p>@lang('errors.exception.code', ['code' => $code])</p>
<p>{!! trans('errors.exception.detail', ['msg' => $message]) !!}</p>
@endsection
