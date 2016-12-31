<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title') - {{ option('site_name') }}</title>
    {!! bs_favicon() !!}
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- App Styles -->
    {!! bs_header('auth') !!}
</head>

<body class="hold-transition login-page">

    @yield('content')

    <!-- App Scripts -->
    {!! bs_footer('auth') !!}

    @yield('script')
</body>
</html>
