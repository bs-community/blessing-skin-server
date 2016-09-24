@extends('errors.general')

@section('title', trans('errors.error-occurred'))

@section('content')
<h1>{{ trans('errors.some-errors') }}</h1>

<p>{{ trans('error.error-code').$code }}</p>
<p>{!! trans('error.details').$message !!}</p>
@endsection
