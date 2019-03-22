@extends('auth.master')

@section('title', trans('auth.bind.title'))

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ option_localized('site_name') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">@lang('auth.bind.message')</p>

        <form method="post" id="login-form">
            @csrf
            <div class="form-group has-feedback">
                <input name="email" type="email" class="form-control" placeholder="@lang('auth.email')">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>

            <p>@lang('auth.bind.introduction')</p>

            @if (isset($msg))
            <div id="msg" class="callout callout-warning">{{ $msg }}</div>
            @endif

            <div class="row">
                <div class="col-xs-8"></div>
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">@lang('auth.bind.button')</button>
                </div><!-- /.col -->
            </div>
        </form>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@endsection
