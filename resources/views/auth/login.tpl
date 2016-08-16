@extends('auth.master')

@section('title', '登录')

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="../">{{ Option::get('site_name') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">登录以管理您的角色及皮肤</p>

        <form id="login-form">
            <div class="form-group has-feedback">
                <input id="email_or_username" type="email" class="form-control" placeholder="邮箱或角色名">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input id="password"type="password" class="form-control" placeholder="密码">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>

            <div class="row" id="captcha-form" style="{{ (\Utils::getValue('login_fails', $_SESSION) > 3) ? '' : 'display: none;'}}">
                <div class="col-xs-8">
                    <div class="form-group has-feedback">
                        <input id="captcha" type="text" class="form-control" placeholder="输入验证码">
                    </div>
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <img class="pull-right captcha" src="./captcha" alt="CAPTCHA" title="点击以更换图片" data-placement="top" data-toggle="tooltip">
                </div>
                <!-- /.col -->
            </div>

            <div id="msg" class="alert hide"></div>

            <div class="row">
                <div class="col-xs-8">
                    <div class="checkbox icheck">
                        <label for="keep">
                            <input id="keep" type="checkbox"> 保持登录状态
                        </label>
                    </div>
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <button id="login-button" class="btn btn-primary btn-block btn-flat">登录</button>
                </div>
                <!-- /.col -->
            </div>
        </form>

        <a href="./forgot">忘记密码了？</a><br>
        <a href="./register" class="text-center">注册新账号</a>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@endsection
