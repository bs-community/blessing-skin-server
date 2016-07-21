@extends('auth.master')

@section('title', '找回密码')

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="../">{{ Option::get('site_name') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">我们将会给您发送一封密码重置邮件</p>

        <form id="login-form">
            <div class="form-group has-feedback">
                <input id="email" type="email" class="form-control" placeholder="Email">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>

            <div class="row" id="captcha-form">
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
                    <a href="./login" class="text-center">我又想起来了</a>
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <button id="forgot-button" class="btn btn-primary btn-block btn-flat">发送</button>
                </div>
                <!-- /.col -->
            </div>
        </form>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@endsection
