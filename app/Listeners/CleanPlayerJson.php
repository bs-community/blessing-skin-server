<?php

namespace App\Listeners;

use Cache;

class CleanPlayerJson
{
    public function handle($event)
    {
        Cache::forget('json-'.$event->player->pid);
    }
}
