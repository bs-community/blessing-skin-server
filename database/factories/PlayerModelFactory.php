<?php

use App\Models\Player;

$factory->define(Player::class, function (Faker\Generator $faker) {
    return [
        'uid' => factory(App\Models\User::class)->create()->uid,
        'player_name' => $faker->firstName,
        'preference' => 'default',
        'last_modified' => $faker->dateTime
    ];
});

$factory->defineAs(Player::class, 'slim', function (Faker\Generator $faker) {
    return [
        'uid' => factory(App\Models\User::class)->create()->uid,
        'player_name' => $faker->firstName,
        'preference' => 'slim',
        'last_modified' => $faker->dateTime
    ];
});
