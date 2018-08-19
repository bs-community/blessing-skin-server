@extends('errors.general')

@section('title', '500 Internal Server Error')

@section('content')
<h1>500 Internal Server Error
    @include('setup.wizard.language')
</h1>

<p>{{ trans('errors.exception.detail', ['msg' => $exception->getMessage() ?: trans('errors.http.msg-500')]) }}</p>
@endsection
