@extends('errors.general')

@section('title', $title)

@section('content')
<h1>{{ $title }}
@include('setup.wizard.language')
</h1>

<p>{{ trans('errors.exception.detail', ['msg' => $message]) }}</p>
@endsection
