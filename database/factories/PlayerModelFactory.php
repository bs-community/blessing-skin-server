<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Player;

$factory->define(Player::class, function (Faker\Generator $faker) {
    return [
        'uid' => factory(App\Models\User::class)->create()->uid,
        'name' => $faker->firstName,
        'tid_skin' => 0,
    ];
});
