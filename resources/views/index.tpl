<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ option('site_name') }}</title>
    <link rel="shortcut icon" href="{{ assets('images/favicon.ico') }}">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- App Styles -->
    {!! bs_header('index') !!}
</head>

<body class="hold-transition {{ option('color_scheme') }} layout-top-nav">
    <div class="wrapper" style="background-image: url('{{ option('home_pic_url') }}');">

        <header class="main-header">
            <nav class="navbar navbar-static-top">
                <div class="container">
                    <div class="navbar-header">
                        <a href="{{ option('site_url') }}" class="navbar-brand">{{ option('site_name') }}</a>
                    </div>

                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                        <ul class="nav navbar-nav">
                            <li class="active"><a href="{{ url('/') }}">{{ trans('general.index') }}</a></li>
                            <li><a href="{{ url('skinlib') }}">{{ trans('general.skinlib') }}</a></li>
                        </ul>
                    </div><!-- /.navbar-collapse -->
                    <!-- Navbar Right Menu -->
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <!-- Language Menu -->
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-language" aria-hidden="true"></i> {{ trans('general.langs') }} <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    @foreach(config('locales') as $locale => $lang)
                                    <li><a href="{{ url('/locale/'.$locale) }}">{{ $lang }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
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
                                <a href="{{ url('auth/login') }}" class="btn btn-login">{{ trans('general.login') }}</a>
                            </li>
                            @endif
                        </ul>
                    </div><!-- /.navbar-custom-menu -->
                </div><!-- /.container-fluid -->
            </nav>
        </header>

        <!-- Full Width Column -->
        <div class="content-wrapper">
            <div class="container">
                <div class="splash">
                    <h1 class="splash-head">{{ option('site_name') }}</h1>
                    <p class="splash-subhead">
                        {{ option('site_description') }}
                    </p>
                    <p>
                        @if (is_null($user))
                        <a href="{{ url('auth/register') }}" class="button">{{ trans('general.register') }}</a>
                        @else
                        <a href="{{ url('user') }}" class="button">{{ trans('general.user-center') }}</a>
                        @endif
                    </p>
                </div>
            </div>
        </div><!-- /.content-wrapper -->

        <!-- Main Footer -->
        <footer class="main-footer">
            <div class="container text-center">
                {!! bs_copyright() !!}
            </div>
        </footer>

    </div><!-- ./wrapper -->

    <!-- App Scripts -->
    {!! bs_footer() !!}
</body>
</html>
