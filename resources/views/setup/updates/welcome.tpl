@extends('setup.updates.master')

@section('content')
<h1>还差一小步</h1>

<p>欢迎升级至 Blessing Skin Server {{ config('app.version') }}！</p>
<p>我们需要升级您的数据库，点击下一步以继续。</p>

<form method="post" action="" novalidate="novalidate">
    <p class="step">
        <input type="submit" name="submit" class="button button-large" value="下一步"  />
    </p>
</form>
@endsection
