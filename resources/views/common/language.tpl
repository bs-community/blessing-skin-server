<!-- Language Menu -->
<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-language" aria-hidden="true"></i>
        <span class="description-text">{{ config('locales.'.App::getLocale(), config('locales.'.config('app.fallback_locale')))['short_name'] }}</span>
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu" role="menu">
        @foreach(config('locales') as $code => $langInfo)
            @if (!isset($langInfo['alias']))
                <?php
                    if (count($_GET) == 0) {
                        $link = "?lang=$code";
                    } elseif(isset($_GET['lang'])) {
                        $link = str_replace("lang={$_GET['lang']}", "lang=$code", $_SERVER['REQUEST_URI']);
                    } else {
                        $link = $_SERVER['REQUEST_URI']."&lang=$code";
                    }
                ?>
                <li class="locale" data-code="{{ $code }}">
                    <a href="{{ $link }}">{{ $langInfo['name'] }}</a>
                </li>
            @endif
        @endforeach
    </ul>
</li>
