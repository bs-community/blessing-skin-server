@extends('errors.general')

@section('title', '403 Forbidden')

@section('content')
<h1>403 Forbidden</h1>

<p>{{ trans('errors.details').$exception->getMessage() ?: trans('error.error403') }}</p>
@endsection
