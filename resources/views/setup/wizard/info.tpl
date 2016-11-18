@extends('setup.wizard.master')

@section('content')
<h1>填写信息</h1>
<p>您需要填写一些基本信息。无需担心填错，这些信息以后可以再次修改。</p>

<form id="setup" method="post" action="{{ url('setup/finish') }}" novalidate="novalidate">
    <table class="form-table">
        <tr>
            <th scope="row"><label for="email">管理员邮箱</label></th>
            <td>
                <input name="email" type="email" id="email" size="25" value="" />
                <p>这是唯一的超级管理员账号，可 添加/取消 其他管理员。</p>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th scope="row"><label for="password">密码</label></th>
            <td>
                <input type="password" name="password" id="password" class="regular-text" autocomplete="off" />
                <p>
                    <span class="description important">
                        <b>重要：</b>您将需要此密码来登录管理皮肤站，请将其保存在安全的位置。
                    </span>
                </p>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th scope="row"><label for="password_confirmation">重复密码</label></th>
            <td>
                <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="off" />
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="site_name">站点名称</label></th>
            <td>
                <input name="site_name" type="text" id="site_name" size="25" value="Blessing Skin Server" />
                <p>
                    <span class="description important">
                        将会显示在首页以及标题栏
                    </span>
                </p>
            </td>
        </tr>
    </table>

    @if (count($errors) > 0)
        <div class="alert alert-warning" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="step">
        <input type="submit" name="submit" id="submit" class="button button-large" value="开始安装"  />
    </p>
</form>
@endsection
