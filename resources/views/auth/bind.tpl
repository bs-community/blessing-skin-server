@extends('auth.master')

@section('title', '绑定邮箱')

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ Option::get('site_name') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">欢迎！您需要填写邮箱以继续使用本站。</p>

        <form  method="post" id="login-form">
            <div class="form-group has-feedback">
                <input name="email" type="email" class="form-control" placeholder="Email">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>

            <p>邮箱仅用于重置密码，我们将不会向您发送任何垃圾邮件。</p>

            @if (isset($msg))
            <div id="msg" class="alert alert-warning">{{ $msg }}</div>
            @endif

            <div class="row">
                <div class="col-xs-8"></div>
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">绑定</button>
                </div><!-- /.col -->
            </div>
        </form>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@endsection
