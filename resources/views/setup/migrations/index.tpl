@extends('setup.migrations.master')

@section('content')
<h1>欢迎</h1>

<p>欢迎使用 Blessing Skin Server 数据迁移工具，此工具用于迁移 v2 的数据至 v3。</p>
<p>目前仅支持从 v2 导入用户皮肤至 v3 的皮肤库中。</p>
<p>更多功能等我有时间再些吧（学业为重</p>

<hr />

<p>选择一个操作以继续：</p>

<p class="step">
    <a href="index.php?action=import-v2-textures" class="button button-large">导入 v2 皮肤库</a>
    <a class="button button-large" disabled="disabled">导入 v2 用户数据</a>
</p>
@endsection
