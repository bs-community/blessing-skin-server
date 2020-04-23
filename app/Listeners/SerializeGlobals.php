<?php

namespace App\Listeners;

use stdClass;

class SerializeGlobals
{
    public function handle($event)
    {
        $blessing = [
            'version' => config('app.version'),
            'locale' => config('app.locale'),
            'base_url' => url('/'),
            'site_name' => option_localized('site_name'),
            'route' => request()->path(),
            'i18n' => new stdClass(),
            'extra' => [],
        ];
        $event->addContent('<script>const blessing = '.json_encode($blessing).';</script>');
    }
}
