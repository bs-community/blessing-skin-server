<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title') - {{ Option::get('site_name') }}</title>
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- App Styles -->
    <link rel="stylesheet" href="{{ url('assets/css/app.min.css') }}?v={{ config('app.version') }}">
    <!-- AdminLTE Skins -->
    <link rel="stylesheet" href="{{ url('assets/libs/skins/'.Option::get('color_scheme').'.min.css') }}?v={{ config('app.version') }}">

    <link rel="stylesheet" href="{{ url('assets/css/user.css') }}?v={{ config('app.version') }}">

    @yield('style')

    <style>{!! Option::get('custom_css') !!}</style>
</head>

<body class="hold-transition {{ Option::get('color_scheme') }} sidebar-mini">
    <div class="wrapper">

        <!-- Main Header -->
        <header class="main-header">

            <!-- Logo -->
            <a href="{{ Option::get('site_url') }}" class="logo">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini"> <i class="fa fa-bookmark"></i> </span>
                <!-- logo for regular state and mobile devices -->
                <span class="logo-lg">{{ Option::get('site_name') }}</span>
            </a>

            <!-- Header Navbar -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>
                <!-- Navbar Right Menu -->
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <!-- User Account Menu -->
                        <li class="dropdown user user-menu">
                            <!-- Menu Toggle Button -->
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-user"></i>
                                <!-- hidden-xs hides the username on small devices so only the image appears. -->
                                <span class="hidden-xs nickname">{{ Utils::getNameOrEmail($user) }}</span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- The user image in the menu -->
                                <li class="user-header">
                                    <img src="../avatar/128/{{ Utils::getAvatarFname($user) }}" alt="User Image">
                                    <p>{{ $user->email }}</p>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="../user/profile" class="btn btn-default btn-flat">我的资料</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="javascript:logout();" class="btn btn-default btn-flat">登出</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
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
                        <img src="../avatar/45/{{ Utils::getAvatarFname($user) }}" alt="User Image">
                    </div>
                    <div class="pull-left info">
                        <p class="nickname">{{ Utils::getNameOrEmail($user) }}</p>
                        <i class="fa fa-circle text-success"></i> Online
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <ul class="sidebar-menu">
                    <li class="header">用户中心</li>
                    <?php $menu = require BASE_DIR."/config/menu.php"; ?>

                    @foreach ($menu['user'] as $key => $value)
                    <li class="{{ ($__env->yieldContent('title') == $value['title']) ? 'active' : '' }}">
                        <a href="{{ $value['link'] }}"><i class="fa {{ $value['icon'] }}"></i> <span>{{ $value['title'] }}</span></a>
                    </li>
                    @endforeach

                    <li class="header">浏览</li>
                    <li><a href="../skinlib"><i class="fa fa-archive"></i> <span>皮肤库</span></a></li>

                    @if ($user->is_admin)
                    <li class="header">管理</li>
                    <li><a href="../admin"><i class="fa fa-cog"></i> <span>管理面板</span></a></li>
                    @endif
                </ul><!-- /.sidebar-menu -->
            </section>
            <!-- /.sidebar -->
        </aside>

        @yield('content')

        <!-- Main Footer -->
        <footer class="main-footer">
            @if (Option::get('show_footer_copyright'))
            <!-- To the right -->
            <div class="pull-right hidden-xs">
                Powered with ❤ by <a href="https://github.com/printempw/blessing-skin-server">Blessing Skin Server</a>.
            </div>
            @endif
            <!-- Default to the left -->
            {!! Utils::getStringReplaced(Option::get('copyright_text'), ['{site_name}' => Option::get('site_name'), '{site_url}' => Option::get('site_url')]) !!}
        </footer>

    </div><!-- ./wrapper -->

    <!-- App Scripts -->
    <script type="text/javascript" src="{{ url('assets/js/app.min.js') }}?v={{ config('app.version') }}"></script>

    <script type="text/javascript" src="{{ url('assets/js/user.js') }}?v={{ config('app.version') }}"></script>

    @yield('script')

    @if (session()->has('msg'))
    <script>
        toastr.info('{{ session('msg') }}'); <?php session()->forget('msg') ?>
    </script>
    @endif

    <script>{!! Option::get('custom_js') !!}</script>
</body>
</html>
