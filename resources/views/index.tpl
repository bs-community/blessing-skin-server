<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ Option::get('site_name') }}</title>
    <link rel="shortcut icon" href="{{ assets('images/favicon.ico') }}">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- App Styles -->
    <link rel="stylesheet" href="{{ assets('css/app.min.css') }}">
    <!-- AdminLTE Skins -->
    <link rel="stylesheet" href="{{ assets('vendor/skins/'.Option::get('color_scheme').'.min.css') }}">

    <link rel="stylesheet" href="{{ assets('css/index.css') }}">

    <style>
        .wrapper {
            background-image: url('{{ Option::get('home_pic_url') }}');
        }

        {!! Option::get('custom_css') !!}
    </style>
</head>

<body class="hold-transition {{ Option::get('color_scheme') }} layout-top-nav">
    <div class="wrapper">

        <header class="main-header">
            <nav class="navbar navbar-static-top">
                <div class="container">
                    <div class="navbar-header">
                        <a href="{{ Option::get('site_url') }}" class="navbar-brand">{{ Option::get('site_name') }}</a>
                    </div>

                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                        <ul class="nav navbar-nav">
                            <li class="active"><a href="{{ url('/') }}">{{ trans('index.index') }}</a></li>
                            <li><a href="{{ url('skinlib') }}">{{ trans('index.skinlib') }}</a></li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-language" aria-hidden="true"></i> {{ trans('index.langs') }} <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    @foreach(config('locales') as $locale => $lang)
                                    <li><a href="{{ url('/locale/'.$locale) }}">{{ $lang }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                        </ul>
                    </div><!-- /.navbar-collapse -->
                    <!-- Navbar Right Menu -->
                        <div class="navbar-custom-menu">
                            <ul class="nav navbar-nav">
                                @if (!is_null($user))
                                <!-- User Account Menu -->
                                <li class="dropdown user user-menu">
                                    <!-- Menu Toggle Button -->
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <!-- The user image in the navbar-->
                                        <img src="{{ avatar($user, 25) }}" class="user-image" alt="User Image">
                                        <!-- hidden-xs hides the username on small devices so only the image appears. -->
                                        <span class="hidden-xs nickname">{{ Utils::getNameOrEmail($user) }}</span>
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
                                                <a href="{{ url('user') }}" class="btn btn-default btn-flat">{{ trans('index.user-center') }}</a>
                                            </div>
                                            <div class="pull-right">
                                                <a href="javascript:logout();" class="btn btn-default btn-flat">{{ trans('index.logout') }}</a>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                @else {{-- Anonymous User --}}
                                <!-- User Account Menu -->
                                <li class="dropdown user user-menu">
                                    <a href="{{ url('auth/login') }}" class="btn btn-login">{{ trans('index.login') }}</a>
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
                    <h1 class="splash-head">{{ Option::get('site_name') }}</h1>
                    <p class="splash-subhead">
                        {{ Option::get('site_description') }}
                    </p>
                    <p>
                        @if (is_null($user))
                        <a href="{{ url('auth/register') }}" class="button">{{ trans('index.register') }}</a>
                        @else
                        <a href="{{ url('user') }}" class="button">{{ trans('index.user-center') }}</a>
                        @endif
                    </p>
                </div>
            </div>
        </div><!-- /.content-wrapper -->

        <!-- Main Footer -->
        <footer class="main-footer">
            <div class="container text-center">
                {!! Utils::getStringReplaced(Option::get('copyright_text'), ['{site_name}' => Option::get('site_name'), '{site_url}' => Option::get('site_url')]) !!}
            </div>
        </footer>

    </div><!-- ./wrapper -->

    <!-- App Scripts -->
    <script type="text/javascript" src="{{ assets('js/app.min.js') }}"></script>

    <script>{!! Option::get('custom_js') !!}</script>
</body>
</html>
