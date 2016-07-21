@extends('auth.master')

@section('title', '重置密码')

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="../">{{ Option::get('site_name') }}</a>
    </div>

    <div class="login-box-body">
        <p class="login-box-msg">{{ $user->getNickName() }}，在这重置你的密码</p>

        <form id="login-form">
            <input id="uid" type="hidden" value="{{ $user->uid }}" />

            <div class="form-group has-feedback">
                <input id="password" type="password" class="form-control" placeholder="密码">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input id="confirm-pwd" type="password" class="form-control" placeholder="重复一遍密码">
                <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
            </div>

            <div id="msg" class="alert hide"></div>

            <div class="row">
                <div class="col-xs-8">
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <button id="reset-button" class="btn btn-primary btn-block btn-flat">重置</button>
                </div>
                <!-- /.col -->
            </div>
        </form>
    </div>
    <!-- /.form-box -->
</div>
<!-- /.login-box -->

@endsection
