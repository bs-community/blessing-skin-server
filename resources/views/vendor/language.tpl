<!-- Language Menu -->
<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-language" aria-hidden="true"></i> Language <span class="caret"></span>
    </a>
    <ul class="dropdown-menu" role="menu">
        @foreach(config('locales') as $locale => $lang)
        <li class="locale"><a href="{{ url('/locale/'.$locale) }}">{{ $lang }}</a></li>
        @endforeach
    </ul>
</li>
