@extends('errors.general')

@section('title', '503 Service Unavailable')

@section('content')
<h1>Be right back.
@include('setup.wizard.language')
</h1>

<p>{{ trans('errors.exception.detail', ['msg' => $exception->getMessage() ?: trans('errors.http.msg-503')]) }}</p>
@endsection
