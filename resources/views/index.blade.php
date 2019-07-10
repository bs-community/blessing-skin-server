<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ option_localized('site_name') }}</title>
    @include('common.favicon')
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('common.seo-meta-tags')
    <!-- App Styles -->
    @include('common.dependencies.style')
    <style>
        .hp-wrapper {
            @if (option('fixed_bg'))
            background-color: rgba(0, 0, 0, 0);
            @else
            background-image: url('{{ $home_pic_url }}');
            @endif
            height: 100vh;
        }

        @if (option('hide_intro'))
        #copyright {
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
            padding: 0 50px 16px 50px;
        }
        @else
        #copyright {
            background: #222;
            padding: 16px 0 20px;
            color: white;
        }
        @endif
    </style>
</head>

<body class="hold-transition {{ option('color_scheme') }} layout-top-nav">

    <div class="hp-wrapper">
        @if (option('fixed_bg'))
        <div id="fixed-bg" style="background-image: url('{{ $home_pic_url }}')"></div>
        @endif
        <!-- Navigation -->
        <header class="main-header {{ $transparent_navbar ? 'transparent' : ''}}">
            <nav class="navbar navbar-fixed-top">
                <div class="container">
                    <div class="navbar-header">
                        <a href="{{ option('site_url') }}" class="navbar-brand">{{ option_localized('site_name') }}</a>
                    </div>

                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                        <ul class="nav navbar-nav">
                            <li class="active"><a href="{{ url('/') }}">@lang('general.index')</a></li>
                            <li><a href="{{ url('skinlib') }}">@lang('general.skinlib')</a></li>
                        </ul>
                    </div><!-- /.navbar-collapse -->
                    <!-- Navbar Right Menu -->
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            @include('common.language')

                            @auth
                                @include('common.user-menu')
                            @else {{-- Anonymous User --}}
                            <!-- User Account Menu -->
                            <li class="dropdown user user-menu">
                                <a href="{{ url('auth/login') }}" class="btn btn-login">@lang('general.login')</a>
                            </li>
                            @endauth
                        </ul>
                    </div><!-- /.navbar-custom-menu -->
                </div><!-- /.container-fluid -->
            </nav>
        </header>

        <div class="container">
            <div class="splash">
                <h1 class="splash-head">{{ option_localized('site_name') }}</h1>
                <p class="splash-subhead">
                    {{ option_localized('site_description') }}
                </p>
                <p>
                    @guest
                        @if (option('user_can_register'))
                        <a href="{{ url('auth/register') }}" id="btn-register" class="button">@lang('general.register')</a>
                        @else
                        <a href="{{ url('auth/login') }}" id="btn-close-register" class="button">@lang('general.login')</a>
                        @endif
                    @else
                    <a href="{{ url('user') }}" class="button">@lang('general.user-center')</a>
                    @endguest
                </p>
            </div>
        </div> <!--/ .container -->

        @if (option('hide_intro'))
        <div id="copyright">
            <!-- Designed by Pratt -->
            <div class="container">
                <!-- YOU CAN NOT MODIFIY THE COPYRIGHT TEXT W/O PERMISSION -->
                <div id="copyright-text" class="pull-right hidden-xs">
                    @include('common.copyright')
                </div>
                <!-- Default to the left -->
                @include('common.custom-copyright')
            </div>
        </div>
        @endif
    </div><!--/ #headerwrap -->

    @if (! option('hide_intro'))
    <!-- INTRO WRAP -->
    <div id="intro">
        <div class="container">
            <div class="row text-center">
                <h1>{!! trans('index.features.title') !!}</h1>
                <br>
                <br>
                <div class="col-lg-4">
                    <i class="fas {{ trans('index.features.first.icon') }}" aria-hidden="true"></i>
                    <h3>{!! trans('index.features.first.name') !!}</h3>
                    <p>{!! trans('index.features.first.desc') !!}</p>
                </div>
                <div class="col-lg-4">
                    <i class="fas {{ trans('index.features.second.icon') }}" aria-hidden="true"></i>
                    <h3>{!! trans('index.features.second.name') !!}</h3>
                    <p>{!! trans('index.features.second.desc') !!}</p>
                </div>
                <div class="col-lg-4">
                    <i class="fas {{ trans('index.features.third.icon') }}" aria-hidden="true"></i>
                    <h3>{!! trans('index.features.third.name') !!}</h3>
                    <p>{!! trans('index.features.third.desc') !!}</p>
                </div>
            </div>
            <br>
        </div> <!--/ .container -->
    </div><!--/ #introwrap -->

    <div id="footerwrap">
        <div class="container">
            <div class="col-lg-6">
                {!! trans('index.introduction', ['sitename' => option_localized('site_name')]) !!}
            </div>

            <div class="col-lg-6">
                <a href="{{ url('auth/register') }}" id="btn-register" class="button">@lang('index.start')</a>
            </div>
        </div>
    </div>
    @endif

    @if (! option('hide_intro'))
    <div id="copyright">
        <!-- Designed by Pratt -->
        <div class="container">
            <!-- YOU CAN NOT MODIFIY THE COPYRIGHT TEXT W/O PERMISSION -->
            <div id="copyright-text" class="pull-right hidden-xs">
                @include('common.copyright')
            </div>
            <!-- Default to the left -->
            @include('common.custom-copyright')
        </div>
    </div>
    @endif

    <script>
    blessing.extra = @json(['transparent_navbar' => (bool) $transparent_navbar])
    </script>

    <!-- App Scripts -->
    @include('common.dependencies.script')
</body>
</html>
