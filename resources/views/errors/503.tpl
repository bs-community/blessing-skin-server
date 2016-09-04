@extends('errors.general')

@section('title', '503 Service Unavailable')

@section('content')
<h1>Be right back.</h1>

<p>详细信息：{{ $exception->getMessage() ?: "Application is now in maintenance mode." }}</p>
@endsection
