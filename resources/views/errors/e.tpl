@extends('errors.general')

@section('title', '出现错误')

@section('content')
<h1>{{ 出现了一些错误：}}</h1>

<p>错误码：  {{ $code }}</p>
<p>详细信息：{{ $message }}</p>
@endsection
