@extends('errors.general')

@section('title', '403 Forbidden')

@section('content')
<h1>403 Forbidden
@include('setup.wizard.language')
</h1>

<p>{{ trans('errors.exception.detail', ['msg' => $exception->getMessage() ?: trans('errors.http.msg-403')]) }}</p>
@endsection
