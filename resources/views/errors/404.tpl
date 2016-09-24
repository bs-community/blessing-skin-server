@extends('errors.general')

@section('title', '404 Not Found')

@section('content')
<h1>404 Not Found</h1>

<p>{{ trans('errors.details').$exception->getMessage() ?: trans('errors.error404') }}</p>
@endsection
