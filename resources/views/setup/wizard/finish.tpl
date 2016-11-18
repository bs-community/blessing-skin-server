@extends('setup.wizard.master')

@section('content')
<h1>成功！</h1>

<p>Blessing Skin Server 安装完成。您是否还沉浸在愉悦的安装过程中？很遗憾，一切皆已完成！ :)</p>
<table class="form-table install-success">
    <tr>
        <th>邮箱</th>
        <td>{{ $email }}</td>
    </tr>
    <tr>
        <th>密码</th>
        <td><p><em>{{ $password }}</em></p></td>
    </tr>
</table>

<p class="step">
    <a href="../" class="button button-large">首页</a>
</p>
@endsection
