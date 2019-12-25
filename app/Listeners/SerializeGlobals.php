<?php

namespace App\Listeners;

class SerializeGlobals
{
    public function handle($event)
    {
        $blessing = [
            'version' => config('app.version'),
            'locale' => config('app.locale'),
            'fallback_locale' => config('app.fallback_locale'),
            'base_url' => url('/'),
            'site_name' => option_localized('site_name'),
            'route' => request()->path(),
            'extra' => [],
        ];
        $event->addContent('<script>const blessing = '.json_encode($blessing).';</script>');
    }
}
