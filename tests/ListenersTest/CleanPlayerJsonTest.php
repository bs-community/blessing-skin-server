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

    public function setUp(): void
    {
        parent::setUp();
        option(['enable_json_cache' => true]);
        app()->register(\App\Providers\EventServiceProvider::class);
    }

    public function testHandle()
    {
        $player = factory(Player::class)->create();
        event(new PlayerProfileUpdated($player));
        Cache::shouldReceive('forget')->with('json-'.$player->pid);
    }
}
