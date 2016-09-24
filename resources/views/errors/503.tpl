@extends('errors.general')

@section('title', '503 Service Unavailable')

@section('content')
<h1>{{ trans('errors.be-right-back') }}</h1>

<p>{{ trans('errors.details').$exception->getMessage() ?: trans('errors.error503') }}</p>
@endsection
