@extends('errors.general')

@section('title', trans('errors.general.title'))

@section('content')
<h1>{{ trans('errors.general.title') }}
@include('setup.wizard.language')
</h1>

<p>{{ $message }}</p>

<p>{!! nl2br(trans('errors.exception.message')) !!}</p>

@endsection
