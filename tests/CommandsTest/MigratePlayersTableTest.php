<?php

namespace Tests;

use Faker;
use Artisan;
use App\Models\Player;
use App\Console\Commands\MigratePlayersTable;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MigratePlayersTableTest extends TestCase
{
    use DatabaseTransactions;

    public function testExecute()
    {
        $faker = Faker\Factory::create();

        for ($i = 1; $i <= 10; $i++) {
            factory(Player::class)->create([
                'tid_skin' => -1,
                'preference' => $faker->randomElement(['default', 'slim']),
                'tid_steve' => $faker->randomDigit(),
                'tid_alex' => $faker->randomDigit(),
            ]);
        }

        Artisan::call('bs:migrate-v4:players-table');

        Player::all()
            ->each(function (Player $player) {
                if ($player->preference == 'default') {
                    $this->assertEquals($player->tid_steve, $player->tid_skin);
                } else {
                    $this->assertEquals($player->tid_alex, $player->tid_skin);
                }
            });
    }
}
