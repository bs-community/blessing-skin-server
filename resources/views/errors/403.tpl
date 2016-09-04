@extends('errors.general')

@section('title', '403 Forbidden')

@section('content')
<h1>403 Forbidden</h1>

<p>详细信息：{{ $exception->getMessage() ?: "你并没有权限查看此页面" }}</p>
@endsection
