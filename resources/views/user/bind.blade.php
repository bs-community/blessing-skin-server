@extends('auth.master')

@section('title', trans('user.player.bind.title'))

@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ option_localized('site_name') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">@lang('user.player.bind.title')</p>
        <form></form>
    </div>
</div>
@endsection
