@extends('auth.master')

@section('title', trans('auth.register.register'))

@section('content')

<div class="register-box">
    <div class="register-logo">
        <a href="{{ url('/') }}">{{ Option::get('site_name') }}</a>
    </div>

    <div class="register-box-body">
        <p class="login-box-msg">{{ trans('auth.register.message', ['sitename' => Option::get('site_name')]) }}</p>

        <form id="register-form">
            <div class="form-group has-feedback">
                <input id="email" type="email" class="form-control" placeholder="{{ trans('auth.email') }}">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input id="password" type="password" class="form-control" placeholder="{{ trans('auth.password') }}">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input id="confirm-pwd" type="password" class="form-control" placeholder="{{ trans('auth.register.repeat-pwd') }}">
                <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
            </div>

            <div class="form-group has-feedback" title="{{ trans('auth.register.nickname-intro') }}" data-placement="top" data-toggle="tooltip">
                <input id="nickname" type="text" class="form-control" placeholder="{{ trans('auth.nickname') }}">
                <span class="glyphicon glyphicon-pencil form-control-feedback"></span>
            </div>

            <div class="row">
                <div class="col-xs-8">
                    <div class="form-group has-feedback">
                        <input id="captcha" type="text" class="form-control" placeholder="{{ trans('auth.captcha') }}">
                    </div>
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <img class="pull-right captcha" src="{{ url('auth/captcha') }}" alt="CAPTCHA" title="{{ trans('auth.change-captcha') }}" data-placement="top" data-toggle="tooltip">
                </div>
                <!-- /.col -->
            </div>

            <div id="msg" class="callout hide"></div>

            <div class="row">
                <div class="col-xs-8">
                    <a href="{{ url('auth/login') }}" class="text-center">{{ trans('auth.login-link') }}</a>
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <button id="register-button" class="btn btn-primary btn-block btn-flat">{{ trans('auth.register.register') }}</button>
                </div>
                <!-- /.col -->
            </div>
        </form>
    </div>
    <!-- /.form-box -->
</div>
<!-- /.register-box -->

@endsection
