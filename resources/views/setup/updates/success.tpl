@extends('setup.updates.master')

@section('content')
<h1>升级成功</h1>

<p>数据库升级成功，欢迎使用 Blessing Skin Server {{ config('app.version') }}！</p>

{{-- if any tip is given --}}
@if (isset($tips))
<p><b>升级提示：</b></p>
<ul>
    @foreach ($tips as $tip)
    <li><p>{{ $tip }}</p></li>
    @endforeach
</ul>
@endif

<p class="step">
    <a href="{{ url('/') }}" class="button button-large">首页</a>
</p>
@endsection
