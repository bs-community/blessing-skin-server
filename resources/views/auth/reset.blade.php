@extends('auth.master')

@section('title', trans('auth.reset.title'))

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ option_localized('site_name') }}</a>
    </div>

    <div class="login-box-body">
        <p class="login-box-msg">@lang('auth.reset.message', ['username' => $user->nickname])</p>

        <form></form>
    </div>
    <!-- /.form-box -->
</div>
<!-- /.login-box -->

@endsection
