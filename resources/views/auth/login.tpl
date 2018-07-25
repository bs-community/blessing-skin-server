@extends('auth.master')

@section('title', trans('auth.login.title'))

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ option_localized('site_name') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">{{ trans('auth.login.message') }}</p>

        @if (Session::has('msg'))
        <div class="callout callout-warning">{{ Session::pull('msg') }}</div>
        @endif

        <form id="login-form">
            <div class="form-group has-feedback">
                <input id="identification" type="email" class="form-control" placeholder="{{ trans('auth.identification') }}">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input id="password" type="password" class="form-control" placeholder="{{ trans('auth.password') }}">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>

            <div class="row" id="captcha-form" style="{{ (Cache::get(sha1('login_fails_'.Utils::getClientIp())) > 3) ? '' : 'display: none;' }}">
                <div class="col-xs-8">
                    <div class="form-group has-feedback">
                        <input id="captcha" type="text" class="form-control" placeholder="{{ trans('auth.captcha') }}">
                    </div>
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <img class="pull-right captcha" src="{{ url('auth/captcha?v='.time()) }}" alt="CAPTCHA" title="{{ trans('auth.change-captcha') }}" data-placement="top" data-toggle="tooltip">
                </div>
                <!-- /.col -->
            </div>

            <div id="msg" class="callout hide"></div>

            <div class="row">
                <div class="col-xs-6">
                    <div class="checkbox icheck" style="margin-top: 0;">
                        <label for="keep">
                            <input id="keep" type="checkbox"> {{ trans('auth.login.keep') }}
                        </label>
                    </div>
                </div><!-- /.col -->
                <div class="col-xs-6">
                    <a class="pull-right" href="{{ url('auth/forgot') }}">{{ trans('auth.forgot-link') }}</a>
                </div><!-- /.col -->
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <button id="login-button" class="btn btn-primary btn-block btn-flat">{{ trans('auth.login.button') }}</button>
                </div><!-- /.col -->
            </div>
        </form>
        <br>
        <a href="{{ url('auth/register') }}" class="pull-left" style="margin-top: -10px;">{{ trans('auth.register-link') }}</a>
    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@endsection
