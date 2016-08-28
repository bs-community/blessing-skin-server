@extends('setup.master')

@section('content')
<h1>欢迎</h1>

<p>欢迎使用 Blessing Skin Server v{{ config('app.version') }}！</p>
<p>成功连接至 MySQL 服务器 {{ $server }}，点击下一步以开始安装。</p>

<p class="step">
    <a href="?step=2" class="button button-large">下一步</a>
</p>
@endsection
