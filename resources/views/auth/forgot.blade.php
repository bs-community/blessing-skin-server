@extends('auth.master')

@section('title', trans('auth.forgot.title'))

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ option_localized('site_name') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">@lang('auth.forgot.message')</p>

        @if (Session::has('msg'))
        <div class="callout callout-warning">{{ Session::pull('msg') }}</div>
        @endif

        <form></form>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->
@include('common.recaptcha')
<script>
Object.defineProperty(blessing, 'extra', {
    get: () => Object.freeze(@json($extra)),
    configurable: false
})
</script>
@endsection
