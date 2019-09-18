<!DOCTYPE html>
<html lang="en">
<head>
    @include('shared.head')
    <title>@yield('title') - {{ option_localized('site_name') }}</title>
    @yield('style')
</head>

<body class="hold-transition {{ option('color_scheme') }} sidebar-mini">
    <div class="wrapper">
        @include('shared.header')
        @include('shared.sidebar', ['scope' => 'admin'])
        @yield('content')
        <footer class="main-footer">
            @include('shared.footer')
        </footer>
    </div>

    @include('shared.foot')
    @yield('script')
</body>
</html>
