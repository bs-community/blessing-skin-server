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
    @include('common.dependencies.style')

    @yield('style')
</head>

<body class="hold-transition {{ option('color_scheme') }} layout-top-nav">
    <div class="wrapper">

        <header class="main-header">
            <nav class="navbar navbar-static-top">
                <div class="container">
                    <div class="navbar-header">
                        <a href="{{ option('site_url') }}" class="navbar-brand">
                            {{ option_localized('site_name') }}
                        </a>
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>

                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                        <ul class="nav navbar-nav">
                            <li class="active">
                                <a href="{{ url('skinlib') }}">@lang('general.skinlib')</a>
                            </li>
                            <li>
                                <a href="{{ url('user/closet') }}">@lang('general.my-closet')</a>
                            </li>
                        </ul>
                    </div><!-- /.navbar-collapse -->
                    <!-- Navbar Right Menu -->
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li><a href="{{ url('skinlib/upload') }}"><i class="fas fa-upload" aria-hidden="true"></i> <span class="description-text">@lang('skinlib.general.upload-new-skin')</span></a></li>

                            @include('common.language')

                            @if (!is_null($user))
                                @include('common.user-menu')
                            @else {{-- Anonymous User --}}
                            <!-- User Account Menu -->
                            <li class="dropdown user user-menu">
                                <!-- Menu Toggle Button -->
                                <a href="{{ url('auth/login') }}">
                                    <i class="fas fa-user"></i>
                                    <!-- hidden-xs hides the username on small devices so only the image appears. -->
                                    <span class="hidden-xs nickname">@lang('general.anonymous')</span>
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
                <!-- YOU CAN NOT MODIFIY THE COPYRIGHT TEXT W/O PERMISSION -->
                <div id="copyright-text" class="pull-right hidden-xs">
                    {!! bs_copyright() !!}
                </div>
                <!-- Default to the left -->
                {!! bs_custom_copyright() !!}
            </div>
        </footer>

    </div><!-- ./wrapper -->

    <!-- App Scripts -->
    @include('common.dependencies.script')

    @yield('script')

    <script>
        if ($('.navbar').height() > 50) {
            $('.description-text').hide();
        }
    </script>
</body>
</html>
