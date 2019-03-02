<?php

use App\Models\Player;

$factory->define(Player::class, function (Faker\Generator $faker) {
    return [
        'uid' => factory(App\Models\User::class)->create()->uid,
        'player_name' => $faker->firstName,
        'tid_skin' => 0,
    ];
});
