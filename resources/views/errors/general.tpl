<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="robots" content="noindex,nofollow" />
    <title>@yield('title') - Blessing Skin Server</title>
    <link rel="stylesheet" type="text/css" href="{{ \Http::getBaseUrl() }}/assets/css/style.css">
</head>

<body class="container">
<p id="logo"><a href="https://github.com/printempw/blessing-skin-server" tabindex="-1">Blessing Skin Server</a></p>

@yield('content')

</body>

<style type="text/css">
    html { background: #f1f1f1; margin: 0 20px; font-weight: 400; }
    body { background: #FFF none repeat scroll 0% 0%; color: #444; font-family: Ubuntu, 'Microsoft Yahei', 'Microsoft Jhenghei', sans-serif; margin: 140px auto 25px; padding: 20px 20px 10px; max-width: 700px; box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.13); }
    h1, h2 { border-bottom: 1px solid #DEDEDE; clear: both; color: #666; font-size: 24px; }
    a:active, a:hover { color: #00a0d2; }
    a { color: #0073aa; }
    #logo a, p, h1, h2 { font-weight: 400; }
    #logo, h1, h2 { padding: 0px 0px 7px; }
    p { padding-bottom: 2px; font-size: 14px; line-height: 1.5; }
    #logo a { font-family: Minecraft, sans-serif; transition: all .2s ease-in-out; font-size: 50px; color: #666; height: 84px; line-height: 1.3em; margin: -130px auto 25px; padding: 0; outline: 0; text-decoration: none; overflow: hidden; display: block; }
    #logo a:hover { color: #42a5f5; }
    #logo { margin: 6px 0 14px; border-bottom: none; text-align: center; }
    /* Mobile phone */
    @media (max-width: 48em) { #logo a { font-size: 40px; } }
    @media (max-width: 35.5em) { #logo a { font-size: 30px; } }
</style>
</html>
