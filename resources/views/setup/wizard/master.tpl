<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="robots" content="noindex,nofollow" />
    <title>{{ trans('setup.wizard.master.title') }}</title>
    <link rel="shortcut icon" href="../resources/assets/dist/images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="../resources/assets/dist/css/install.css">
    @yield('style')
</head>

<body class="container">
<p id="logo"><a href="https://github.com/printempw/blessing-skin-server" tabindex="-1">Blessing Skin Server</a></p>

@yield('content')

<script>
    function refreshWithLangPrefer() {
        var e = document.getElementById("language-chooser");
        var lang = e.options[e.selectedIndex].value;

        window.location = "?lang="+lang;
    }
</script>

@yield('script')

</body>
</html>
