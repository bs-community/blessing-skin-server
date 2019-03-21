<?php

namespace App\Listeners;

use Cache;
use Storage;
use App\Models\Player;
use App\Events\GetPlayerJson;
use App\Events\PlayerProfileUpdated;
use Illuminate\Contracts\Events\Dispatcher;

class CachePlayerJson
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(GetPlayerJson::class, [$this, 'remember']);
        $events->listen(PlayerProfileUpdated::class, [$this, 'forget']);
    }

    public function remember(GetPlayerJson $event)
    {
        $key = "json-{$event->player->pid}-{$event->apiType}";
        $content = Cache::rememberForever($key, function () use ($event) {
            return $event->player->generateJsonProfile($event->apiType);
        });

        return $content;
    }

    public function forget(PlayerProfileUpdated $event)
    {
        Cache::forget("json-{$event->player->pid}-".Player::CSL_API);
        Cache::forget("json-{$event->player->pid}-".Player::USM_API);
    }
}
