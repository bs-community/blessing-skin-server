@extends('setup.updates.master')

@section('content')
<h1>还差一小步</h1>

<p>欢迎升级至 Blessing Skin Server {{ App::getVersion() }}！</p>
<p>我们需要升级您的数据库，点击下一步以继续。</p>

<p class="step">
    <a href="update.php?step=2" class="button button-large">下一步</a>
</p>
@endsection
