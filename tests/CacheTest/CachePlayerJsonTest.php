<?php

namespace Tests;

use Cache;
use Event;
use App\Models\Player;
use App\Events\GetPlayerJson;
use Illuminate\Http\UploadedFile;
use App\Listeners\CachePlayerJson;
use App\Events\PlayerProfileUpdated;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CachePlayerJsonTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        Event::subscribe(CachePlayerJson::class);
    }

    public function testRemember()
    {
        $player = factory(Player::class)->create();
        event(new GetPlayerJson($player, Player::CSL_API));
        $this->assertTrue(Cache::has("json-{$player->pid}-".Player::CSL_API));
    }

    public function testForget()
    {
        $player = factory(Player::class)->create();
        event(new PlayerProfileUpdated($player));
        Cache::shouldReceive('forget')
            ->with("json-{$player->pid}-".Player::CSL_API)
            ->with("json-{$player->pid}-".Player::USM_API);
    }
}
