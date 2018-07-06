<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title') - {{ option_localized('site_name') }}</title>
    {!! bs_favicon() !!}
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- App Styles -->
    @include('common.dependencies.style', ['module' => 'auth'])

    <script>blessing.redirect_to = "{{ session('last_requested_path') }}";</script>
</head>

<body class="hold-transition login-page">

    @yield('content')

    <!-- YOU CAN NOT MODIFIY THE COPYRIGHT TEXT W/O PERMISSION -->
    <div id="copyright-text" class="hide">
        {!! bs_copyright() !!}
    </div>

    <!-- App Scripts -->
    @include('common.dependencies.script', ['module' => 'auth'])

    @yield('script')
</body>
</html>
