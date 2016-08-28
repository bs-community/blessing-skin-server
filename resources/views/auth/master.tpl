<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title') - {{ Option::get('site_name') }}</title>
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- App Styles -->
    <link rel="stylesheet" href="../assets/css/app.min.css">
    <link rel="stylesheet" href="../assets/libs/skins/{{ Option::get('color_scheme') }}.min.css">

    <link rel="stylesheet" href="../assets/css/auth.css">

    <style>{{ Option::get('custom_css')  }}</style>
</head>

<body class="hold-transition login-page">

    @yield('content')

    <!-- App Scripts -->
    <script type="text/javascript" src="../assets/js/app.min.js"></script>

    <script type="text/javascript" src="../assets/js/auth.js"></script>

    @if (Session::has('msg'))
    <script>
        toastr.info('{{ session('msg') }}');
    </script>
    @endif

    <script>{!! Option::get('custom_js') !!}</script>
</body>
</html>
