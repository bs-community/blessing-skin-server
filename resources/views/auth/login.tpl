@extends('auth.master')

@section('title', trans('auth.login.title'))

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ option_localized('site_name') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">@lang('auth.login.message')</p>

        @if (Session::has('msg'))
        <div class="callout callout-warning">{{ Session::pull('msg') }}</div>
        @endif

        <form></form>
        <br>
        <a href="{{ url('auth/register') }}" class="pull-left" style="margin-top: -10px;">@lang('auth.register-link')</a>
    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<script>
Object.defineProperty(window, '__bs_data__', {
    get: function () {
        return Object.freeze({ tooManyFails: {{ session('login_fails') > 3 ? 'true' : 'false' }} })
    }
})
</script>

@endsection
