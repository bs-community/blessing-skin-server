<?php

namespace App\Listeners;

use App\Events;
use App\Models\Player;
use Cache;
use Illuminate\Contracts\Events\Dispatcher;

class CachePlayerJson
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Events\GetPlayerJson::class, [$this, 'remember']);
        $events->listen(Events\PlayerProfileUpdated::class, [$this, 'forget']);
    }

    public function remember($event)
    {
        $key = "json-{$event->player->pid}-{$event->apiType}";
        $content = Cache::rememberForever($key, function () use ($event) {
            return $event->player->generateJsonProfile($event->apiType);
        });

        return $content;
    }

    public function forget($event)
    {
        Cache::forget("json-{$event->player->pid}-".Player::CSL_API);
        Cache::forget("json-{$event->player->pid}-".Player::USM_API);
    }
}
