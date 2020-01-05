<?php

namespace Tests;

use App\Events\PlayerProfileUpdated;
use App\Models\Player;
use Cache;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CleanPlayerJsonTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        option(['enable_json_cache' => true]);
        $provider = new \App\Providers\EventServiceProvider(app());
        $provider->boot();

        $player = factory(Player::class)->create();
        event(new PlayerProfileUpdated($player));
        Cache::shouldReceive('forget')->with('json-'.$player->pid);
    }
}
