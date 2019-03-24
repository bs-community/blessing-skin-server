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
@include('common.recaptcha')
<script>
Object.defineProperty(blessing, 'extra', {
    configurable: false,
    get: () => Object.freeze(@json([
        'tooManyFails' => cache(sha1('login_fails_'.get_client_ip())) > 3,
        'recaptcha' => option('recaptcha_sitekey'),
    ]))
})
</script>

@endsection
