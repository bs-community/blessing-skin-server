<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title') - {{ option('site_name') }}</title>
    <link rel="shortcut icon" href="{{ assets('images/favicon.ico') }}">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- App Styles -->
    {!! bs_header('skinlib') !!}

    @yield('style')
</head>

<body class="hold-transition {{ option('color_scheme') }} layout-top-nav">
    <div class="wrapper">

        <header class="main-header">
            <nav class="navbar navbar-static-top">
                <div class="container">
                    <div class="navbar-header">
                        <a href="{{ option('site_url') }}" class="navbar-brand">{{ option('site_name') }}</a>
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                            <i class="fa fa-bars"></i>
                        </button>
                    </div>

                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                        <ul class="nav navbar-nav">
                            <li class="active"><a href="{{ url('skinlib') }}">{{ trans('general.skinlib') }}</a></li>
                            <li><a href="{{ url('user/closet') }}">{{ trans('general.my-closet') }}</a></li>

                            @unless (isset($with_out_filter))
                            <!-- Filters -->
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-filter" aria-hidden="true"></i> {{ trans('skinlib.general.filter') }} <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href='?{{ isset($_GET["q"]) ? "q=$q&" : "" }}filter=skin&sort={{ $sort }}'>{{ trans('general.skin') }} <small>{{ trans('skinlib.filter.any-model') }}</small></a></li>
                                    <li><a href='?{{ isset($_GET["q"]) ? "q=$q&" : "" }}filter=steve&sort={{ $sort }}'>{{ trans('general.skin') }} <small>{{ trans('skinlib.filter.steve-model') }}</small></a></li>
                                    <li><a href='?{{ isset($_GET["q"]) ? "q=$q&" : "" }}filter=alex&sort={{ $sort }}'>{{ trans('general.skin') }} <small>{{ trans('skinlib.filter.alex-model') }}</small></a></li>
                                    <li class="divider"></li>
                                    <li><a href='?{{ isset($_GET["q"]) ? "q=$q&" : "" }}filter=cape&sort={{ $sort }}'>{{ trans('general.cape') }}</a></li>
                                    @if (!is_null($user))
                                    <li class="divider"></li>
                                    <li><a href="?filter=user&uid={{ $user->uid }}&sort={{ $sort }}">{{ trans('skinlib.general.my-upload') }}</a></li>
                                    @endif
                                </ul>
                            </li>

                            <!-- Sort -->
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-sort-amount-desc" aria-hidden="true"></i> {{ trans('skinlib.general.sort') }} <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href='?{{ isset($_GET["q"]) ? "q=$q&" : "" }}filter={{ $filter }}{{ isset($_GET['uid']) ? "&uid={$_GET['uid']}" : "" }}&sort=likes'>{{ trans('skinlib.sort.most-liked') }}</a></li>
                                    <li class="divider"></li>
                                    <li><a href='?{{ isset($_GET["q"]) ? "q=$q&" : "" }}filter={{ $filter }}{{ isset($_GET['uid']) ? "&uid={$_GET['uid']}" : "" }}&sort=time'>{{ trans('skinlib.sort.newest-uploaded') }}</a></li>
                                </ul>
                            </li>
                            @endunless

                            @include('vendor.language')
                        </ul>
                        <form class="navbar-form navbar-left" role="search" action="{{ url('skinlib/search') }}">
                            <div class="form-group">
                                <input type="text" class="form-control" id="navbar-search-input" name="q" placeholder="{{ trans('skinlib.general.search-textures') }}" value="{{ $q or '' }}" />
                            </div>
                        </form>
                    </div><!-- /.navbar-collapse -->
                    <!-- Navbar Right Menu -->
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li><a href="{{ url('skinlib/upload') }}"><i class="fa fa-upload" aria-hidden="true"></i> {{ trans('skinlib.general.upload-new-skin') }}</a></li>
                            @if (!is_null($user))
                            <!-- User Account Menu -->
                            <li class="dropdown user user-menu">
                                <!-- Menu Toggle Button -->
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <!-- The user image in the navbar-->
                                    <img src="{{ avatar($user, 25) }}" class="user-image" alt="User Image">
                                    <!-- hidden-xs hides the username on small devices so only the image appears. -->
                                    <span class="hidden-xs nickname">{{ bs_nickname($user) }}</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- The user image in the menu -->
                                    <li class="user-header">
                                        <img src="{{ avatar($user, 128) }}" alt="User Image">
                                        <p>{{ $user->email }}</p>
                                    </li>
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="{{ url('user') }}" class="btn btn-default btn-flat">{{ trans('general.user-center') }}</a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="javascript:logout();" class="btn btn-default btn-flat">{{ trans('general.logout') }}</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            @else {{-- Anonymous User --}}
                            <!-- User Account Menu -->
                            <li class="dropdown user user-menu">
                                <!-- Menu Toggle Button -->
                                <a href="{{ url('auth/login') }}">
                                    <i class="fa fa-user"></i>
                                    <!-- hidden-xs hides the username on small devices so only the image appears. -->
                                    <span class="hidden-xs nickname">{{ trans('general.anonymous') }}</span>
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
                @if (option('show_footer_copyright'))
                <!-- To the right -->
                <div class="pull-right hidden-xs">
                    Powered with ❤ by <a href="https://github.com/printempw/blessing-skin-server">Blessing Skin Server</a>.
                </div>
                @endif
                <!-- Default to the left -->
                {!! bs_copyright() !!}
            </div>
        </footer>

    </div><!-- ./wrapper -->

    <!-- App Scripts -->
    {!! bs_footer('skinlib') !!}

    @yield('script')
</body>
</html>
