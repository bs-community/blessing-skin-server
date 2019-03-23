@extends('auth.master')

@section('title', trans('auth.register.title'))

@section('content')

<div class="register-box">
    <div class="register-logo">
        <a href="{{ url('/') }}">{{ option_localized('site_name') }}</a>
    </div>

    <div class="register-box-body">
        <p class="login-box-msg">@lang('auth.register.message', ['sitename' => option_localized('site_name')])</p>

        <form></form>
    </div>
    <!-- /.form-box -->
</div>
<!-- /.register-box -->

<script>
Object.defineProperty(blessing, 'extra', {
    get: () => Object.freeze(@json($extra)),
    configurable: false
})
</script>
@endsection
