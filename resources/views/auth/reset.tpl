@extends('auth.master')

@section('title', trans('auth.reset.title'))

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ Option::get('site_name') }}</a>
    </div>

    <div class="login-box-body">
        <p class="login-box-msg">{{ trans('auth.reset.message', ['username' => $user->getNickName()]) }}</p>

        <form id="login-form">
            <input id="uid" type="hidden" value="{{ $user->uid }}" />

            <div class="form-group has-feedback">
                <input id="password" type="password" class="form-control" placeholder="{{ trans('auth.password') }}">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input id="confirm-pwd" type="password" class="form-control" placeholder="{{ trans('auth.register.repeat-pwd') }}">
                <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
            </div>

            <div id="msg" class="callout hide"></div>

            <div class="row">
                <div class="col-xs-8">
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <button id="reset-button" class="btn btn-primary btn-block btn-flat">{{ trans('auth.reset.button') }}</button>
                </div>
                <!-- /.col -->
            </div>
        </form>
    </div>
    <!-- /.form-box -->
</div>
<!-- /.login-box -->

@endsection
