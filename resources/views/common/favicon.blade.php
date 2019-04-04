@php
    $faviconUrl = Str::startsWith($url = (option('favicon_url') ?: config('options.favicon_url')), 'http') ? $url : url($url);
@endphp
<link rel="shortcut icon" href="{{ $faviconUrl }}">
<link rel="icon" type="image/png" href="{{ $faviconUrl }}" sizes="192x192">
<link rel="apple-touch-icon" href="{{ $faviconUrl }}" sizes="180x180">
