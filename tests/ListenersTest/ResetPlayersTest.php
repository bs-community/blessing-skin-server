<?php

namespace Tests;

use App\Models\Player;
use App\Models\Texture;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ResetPlayersTest extends TestCase
{
    use DatabaseTransactions;

    public function testPublicTexture()
    {
        $texture = Texture::factory()->create();
        $player = Player::factory()->create(['tid_skin' => $texture->tid]);

        event('texture.privacy.updated', [$texture]);
        $player->refresh();
        $this->assertEquals($texture->tid, $player->tid_skin);
    }

    public function testPrivateTexture()
    {
        $texture = Texture::factory()->private()->create();
        $player = Player::factory()->create(['tid_skin' => $texture->tid]);
        $playerOfUploader = Player::factory()->create([
            'uid' => $texture->uploader,
            'tid_skin' => $texture->tid,
        ]);

        event('texture.privacy.updated', [$texture]);
        $player->refresh();
        $playerOfUploader->refresh();
        $texture->refresh();
        $this->assertEquals(0, $player->tid_skin);
        $this->assertEquals($texture->tid, $playerOfUploader->tid_skin);
    }

    public function testDeletedTexture()
    {
        $texture = Texture::factory()->create();
        $player = Player::factory()->create(['tid_skin' => $texture->tid]);
        $playerOfUploader = Player::factory()->create([
            'uid' => $texture->uploader,
            'tid_skin' => $texture->tid,
        ]);

        $texture->delete();
        event('texture.deleted', [$texture]);
        $player->refresh();
        $playerOfUploader->refresh();
        $texture->refresh();
        $this->assertEquals(0, $player->tid_skin);
        $this->assertEquals(0, $playerOfUploader->tid_skin);
    }
}
