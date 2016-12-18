@extends('errors.general')

@section('title', trans('errors.general.title'))

@section('content')
<h1>{{ trans('errors.general.title') }}</h1>

<p>{{ trans('errors.brief.message') }}</p>

@endsection
