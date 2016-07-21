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
    <link rel="stylesheet" href="../assets/dist/app.min.css">
    <!-- AdminLTE Skins -->
    <link rel="stylesheet" href="../assets/libs/skins/{{ Option::get('color_scheme') }}.min.css">

    <link rel="stylesheet" href="../assets/dist/css/skinlib.min.css">

    @yield('style')

    <style>{{ Option::get('custom_css') }}</style>
</head>

<body class="hold-transition {{ Option::get('color_scheme') }} layout-top-nav">
    <div class="wrapper">

        <header class="main-header">
            <nav class="navbar navbar-static-top">
                <div class="container">
                    <div class="navbar-header">
                        <a href="{{ Option::get('site_url') }}" class="navbar-brand">{{ Option::get('site_name') }}</a>
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                            <i class="fa fa-bars"></i>
                        </button>
                    </div>

                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                        <ul class="nav navbar-nav">
                            <li class="active"><a href="../skinlib">皮肤库</a></li>
                            <li><a href="../user/closet">我的衣柜</a></li>
                            @unless (isset($with_out_filter))
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-filter" aria-hidden="true"></i> 过滤器 <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href='?{{ isset($_GET["q"]) ? "q=$q&" : "" }}filter=skin&sort={{ $sort }}'>皮肤<small>（任意模型）</small></a></li>
                                    <li><a href='?{{ isset($_GET["q"]) ? "q=$q&" : "" }}filter=steve&sort={{ $sort }}'>皮肤<small>（Steve 模型）</small></a></li>
                                    <li><a href='?{{ isset($_GET["q"]) ? "q=$q&" : "" }}filter=alex&sort={{ $sort }}'>皮肤<small>（Alex 模型）</small></a></li>
                                    <li class="divider"></li>
                                    <li><a href='?{{ isset($_GET["q"]) ? "q=$q&" : "" }}filter=cape&sort={{ $sort }}'>披风</a></li>
                                    @if (!is_null($user))
                                    <li class="divider"></li>
                                    <li><a href="?filter=user&uid={{ $user->uid }}&sort={{ $sort }}">我的上传</a></li>
                                    @endif
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-sort-amount-desc" aria-hidden="true"></i> 排序 <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href='?{{ isset($_GET["q"]) ? "q=$q&" : "" }}filter={{ $filter }}{{ isset($_GET['uid']) ? "&uid={$_GET['uid']}" : "" }}&sort=likes'>最多收藏</a></li>
                                    <li class="divider"></li>
                                    <li><a href='?{{ isset($_GET["q"]) ? "q=$q&" : "" }}filter={{ $filter }}{{ isset($_GET['uid']) ? "&uid={$_GET['uid']}" : "" }}&sort=time'>最新上传</a></li>
                                </ul>
                            </li>
                            @endunless
                        </ul>
                        <form class="navbar-form navbar-left" role="search" action="../skinlib/search">
                            <div class="form-group">
                                <input type="text" class="form-control" id="navbar-search-input" name="q" placeholder="搜索材质" value="{{ $q or '' }}" />
                            </div>
                        </form>
                    </div><!-- /.navbar-collapse -->
                    <!-- Navbar Right Menu -->
                        <div class="navbar-custom-menu">
                            <ul class="nav navbar-nav">
                                @unless (isset($with_out_filter))
                                <li><a href="./skinlib/upload"><i class="fa fa-upload" aria-hidden="true"></i> 上传新皮肤</a></li>
                                @endunless
                                @if (!is_null($user))
                                <!-- User Account Menu -->
                                <li class="dropdown user user-menu">
                                    <!-- Menu Toggle Button -->
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <!-- The user image in the navbar-->
                                        <img src="../avatar/25/{{ base64_encode($_SESSION['email']) }}.png" class="user-image" alt="User Image">
                                        <!-- hidden-xs hides the username on small devices so only the image appears. -->
                                        <span class="hidden-xs nickname">{{ ($user->getNickName() == '') ? $_SESSION['email'] : $user->getNickName() }}</span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <!-- The user image in the menu -->
                                        <li class="user-header">
                                            <img src="../avatar/128/{{ base64_encode($_SESSION['email']) }}.png" alt="User Image">
                                            <p>{{ $_SESSION['email'] }}</p>
                                        </li>
                                        <!-- Menu Footer-->
                                        <li class="user-footer">
                                            <div class="pull-left">
                                                <a href="../user" class="btn btn-default btn-flat">用户中心</a>
                                            </div>
                                            <div class="pull-right">
                                                <a href="javascript:logout();" class="btn btn-default btn-flat">登出</a>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                @else {{-- Anonymous User --}}
                                <!-- User Account Menu -->
                                <li class="dropdown user user-menu">
                                    <!-- Menu Toggle Button -->
                                    <a href="../auth/login">
                                        <i class="fa fa-user"></i>
                                        <!-- hidden-xs hides the username on small devices so only the image appears. -->
                                        <span class="hidden-xs nickname">未登录</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div><!-- /.navbar-custom-menu -->
                </div><!-- /.container-fluid -->
            </nav>
        </header>

        @yield('content')

        <!-- Main Footer -->
        <footer class="main-footer">
            <div class="container">
                @if (Option::get('show_footer_copyright'))
                <!-- To the right -->
                <div class="pull-right hidden-xs">
                    Powered with ❤ by <a href="https://github.com/printempw/blessing-skin-server">Blessing Skin Server</a>.
                </div>
                @endif
                <!-- Default to the left -->
                <strong>Copyright &copy; 2016 <a href="{{ Option::get('site_url') }}">{{ Option::get('site_name') }}</a>.</strong> All rights reserved.
            </div>
        </footer>

    </div><!-- ./wrapper -->

    <!-- App Scripts -->
    <script type="text/javascript" src="../assets/dist/app.min.js"></script>

    <script type="text/javascript" src="../assets/dist/js/skinlib.min.js"></script>

    @yield('script')

    <script>{{ Option::get('custom_js') }}</script>
</body>
</html>
