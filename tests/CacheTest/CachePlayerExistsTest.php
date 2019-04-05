<?php

namespace Tests;

use Cache;
use Event;
use App\Events;
use App\Models\Player;
use Illuminate\Http\UploadedFile;
use App\Listeners\CachePlayerExists;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CachePlayerExistsTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        option(['enable_notfound_cache' => true]);
        $provider = new \App\Providers\EventServiceProvider(app());
        $provider->boot();
    }

    public function testRemember()
    {
        $player = factory(Player::class)->create();
        Cache::shouldReceive('get')
            ->times(2)
            ->andReturn(null)
            ->andReturn(null);
        Cache::shouldReceive('forever')->once()->with('notfound-nope', '1');

        event(new Events\CheckPlayerExists(null));
        event(new Events\CheckPlayerExists($player->name));
        event(new Events\CheckPlayerExists('nope'));
    }

    public function testForget()
    {
        $player = factory(Player::class)->create();
        event(new Events\PlayerWasAdded($player));
        Cache::shouldReceive('forget')->with("notfound-{$player->name}");
    }
}
