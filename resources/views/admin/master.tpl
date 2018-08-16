<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title') - {{ option_localized('site_name') }}</title>
    {!! bs_favicon() !!}
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- App Styles -->
    @include('common.dependencies.style', ['module' => 'admin'])

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

                <!-- Sidebar user panel (optional) -->
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="{{ avatar($user, 45) }}" alt="User Image">
                    </div>
                    <div class="pull-left info">
                        <p class="nickname">{{ bs_nickname($user) }}</p>
                        <i class="fas fa-circle text-success"></i> {{ bs_role($user) }}
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <ul class="sidebar-menu tree" data-widget="tree">
                    <li class="header">@lang('general.admin-panel')</li>
                    {!! bs_menu('admin') !!}

                    <li class="header">@lang('general.back')</li>
                    <li><a href="{{ url('user') }}"><i class="fas fa-user"></i> &nbsp;<span>@lang('general.user-center')</span></a></li>
                </ul><!-- /.sidebar-menu -->
            </section>
            <!-- /.sidebar -->
        </aside>

        @yield('content')

        <!-- Main Footer -->
        <footer class="main-footer">
            <!-- YOU CAN NOT MODIFIY THE COPYRIGHT TEXT W/O PERMISSION -->
            <div id="copyright-text" class="pull-right hidden-xs">
                {!! bs_copyright() !!}
            </div>
            <!-- Default to the left -->
            {!! bs_custom_copyright() !!}
        </footer>

    </div><!-- ./wrapper -->

    <!-- App Scripts -->
    @include('common.dependencies.script')

    @if (option('check_update'))
    <script>$(document).ready(checkForUpdates);</script>
    @endif

    @if (option('allow_sending_statistics'))
    <script>sendFeedback();</script>
    @endif

    @yield('script')
</body>
</html>
