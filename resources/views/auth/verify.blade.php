@extends('auth.master')

@section('title', trans('auth.verify.title'))

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ option_localized('site_name') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">{{ trans('auth.verify.message', ['sitename' => option_localized('site_name')]) }}</p>

        <div class="callout callout-success">
            <i class="icon fa fa-check"></i> {{ trans('auth.verify.success') }}
        </div>

        <div class="row">
            <div class="col-xs-6 pull-right">
                <a href="{{ url('/') }}" class="btn btn-primary btn-block btn-flat">
                    {{ trans('auth.verify.button') }}
                </a>
            </div><!-- /.col -->
        </div>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@endsection
