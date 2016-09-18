@extends('errors.general')

@section('title', trans('errors.error-occurred'))

@section('content')
<h1>{{ $level.': '.trans('errors.some-errors') }}</h1>

<p>{{ trans('errors.details').$message }}</p>

<p>{{ trans('errors.file-location') }}<b>{{ $file }}: {{ $line }}</b></p>

@endsection
