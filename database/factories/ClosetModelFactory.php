<?php

use App\Models\User;
use App\Models\Closet;

$factory->define(Closet::class, function (Faker\Generator $faker) {
    return [
        'uid' => factory(User::class)->create()->uid,
        'textures' => '[]',
    ];
});
