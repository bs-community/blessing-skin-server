@extends('auth.master')

@section('title', '注册')

@section('content')

<div class="register-box">
    <div class="register-logo">
        <a href="../">{{ Option::get('site_name') }}</a>
    </div>

    <div class="register-box-body">
        <p class="login-box-msg">欢迎使用 {{ Option::get('site_name') }}！</p>

        <form id="register-form">
            <div class="form-group has-feedback">
                <input id="email" type="email" class="form-control" placeholder="邮箱">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input id="password" type="password" class="form-control" placeholder="密码">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input id="confirm-pwd" type="password" class="form-control" placeholder="重复一遍密码">
                <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
            </div>

            <div class="form-group has-feedback" title="昵称可使用汉字，随时可以修改" data-placement="top" data-toggle="tooltip">
                <input id="nickname" type="text" class="form-control" placeholder="昵称">
                <span class="glyphicon glyphicon-pencil form-control-feedback"></span>
            </div>

            <div class="row">
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
                    <a href="./login" class="text-center">已经有账号了？登录</a>
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <button id="register-button" class="btn btn-primary btn-block btn-flat">注册</button>
                </div>
                <!-- /.col -->
            </div>
        </form>
    </div>
    <!-- /.form-box -->
</div>
<!-- /.register-box -->

@endsection
