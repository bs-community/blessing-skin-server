<?php

namespace App\Listeners;

use Cache;
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

    public function remember($event)
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

    public function forget($event)
    {
        Cache::forget("notfound-{$event->player->name}");
    }
}
