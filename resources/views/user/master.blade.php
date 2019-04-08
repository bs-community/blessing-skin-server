<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title') - {{ option_localized('site_name') }}</title>
    @include('common.favicon')
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App Styles -->
    @include('common.dependencies.style')

    @yield('style')
</head>

@php
    $user = auth()->user();
@endphp

<body class="hold-transition {{ option('color_scheme') }} sidebar-mini">
    <div class="wrapper">

        <!-- Main Header -->
        <header class="main-header">

            <!-- Logo -->
            <a href="{{ option('site_url') }}" class="logo">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini"> <i class="fas fa-bookmark"></i> </span>
                <!-- logo for regular state and mobile devices -->
                <span class="logo-lg">{{ option_localized('site_name') }}</span>
            </a>

            <!-- Header Navbar -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <i class="fas fa-bars"></i>
                    <span class="sr-only">Toggle navigation</span>
                </a>
                <!-- Navbar Right Menu -->
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        @include('common.language')

                        @include('common.user-menu')
                    </ul>
                </div>
            </nav>
        </header>
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">

            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                @include('common.user-panel')
                <!-- Sidebar Menu -->
                <ul class="sidebar-menu tree" data-widget="tree">
                    <li class="header">@lang('general.user-center')</li>
                    {!! bs_menu('user') !!}

                    <li class="header">@lang('general.explore')</li>
                    <li><a href="{{ url('skinlib') }}"><i class="fas fa-archive"></i> &nbsp;<span>@lang('general.skinlib')</span></a></li>

                    @admin($user)
                    <li class="header">@lang('general.manage')</li>
                    <li><a href="{{ url('admin') }}"><i class="fas fa-cog"></i> &nbsp;<span>@lang('general.admin-panel')</span></a></li>
                    @endadmin
                </ul><!-- /.sidebar-menu -->
            </section>
            <!-- /.sidebar -->
        </aside>

        @yield('content')

        <!-- Main Footer -->
        <footer class="main-footer">
            <!-- YOU CAN NOT MODIFIY THE COPYRIGHT TEXT W/O PERMISSION -->
            <div id="copyright-text" class="pull-right hidden-xs">
                @include('common.copyright')
            </div>
            <!-- Default to the left -->
            @include('common.custom-copyright')
        </footer>

    </div><!-- ./wrapper -->

    <!-- App Scripts -->
    @include('common.dependencies.script')

    @yield('script')
</body>
</html>
