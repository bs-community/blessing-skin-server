@extends('setup.master')

@section('content')
<h1>填写信息</h1>
<p>您需要填写一些基本信息。无需担心填错，这些信息以后可以再次修改。</p>

<form id="setup" method="post" action="index.php?step=3" novalidate="novalidate">
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
            <th scope="row"><label for="confirm-pwd">重复密码</label></th>
            <td>
                <input type="password" name="confirm-pwd" id="confirm-pwd" autocomplete="off" />
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sitename">站点名称</label></th>
            <td>
                <input name="sitename" type="text" id="sitename" size="25" value="" />
                <p>
                    <span class="description important">
                        将会显示在首页以及标题栏，最好用纯英文（字体原因）
                    </span>
                </p>
            </td>
        </tr>
    </table>

@if (session()->has('msg'))
<div class="alert alert-warning" role="alert">{{ session('msg') }}</div>
<?php session()->forget('msg'); ?>
@endif

    <p class="step">
        <input type="submit" name="Submit" id="submit" class="button button-large" value="开始安装"  />
    </p>
</form>
@endsection
