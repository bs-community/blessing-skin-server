@extends('errors.general')

@section('title', '404 Not Found')

@section('content')
<h1>404 Not Found</h1>

<p>详细信息：{{ $message or "这里啥都没有哦" }}</p>
@endsection
