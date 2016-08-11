@extends('setup.updates.master')

@section('content')
<h1>升级成功</h1>

<p>数据库升级成功，欢迎使用 Blessing Skin Server {{ App::getVersion() }}！</p>

<p class="step">
    <a href="../" class="button button-large">首页</a>
</p>
@endsection
