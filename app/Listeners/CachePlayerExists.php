<?php

namespace App\Listeners;

use Cache;
use Storage;
use App\Events;
use App\Models\Player;
use Illuminate\Events\Dispatcher;

class CachePlayerExists
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Events\CheckPlayerExists::class, [$this, 'remember']);
        $events->listen(Events\PlayerWasAdded::class, [$this, 'forget']);
    }

    public function remember(Events\CheckPlayerExists $event)
    {
        $key = "notfound-{$event->playerName}";

        if ($event->playerName && is_null(Cache::get($key))) {
            $player = Player::where('name', $event->playerName)->first();

            if (! $player) {
                Cache::forever($key, '1');
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function forget(Events\PlayerWasAdded $event)
    {
        Cache::forget("notfound-{$event->player->name}");
    }
}
