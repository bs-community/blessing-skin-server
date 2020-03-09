<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Texture;

$factory->define(Texture::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->firstName,
        'type' => 'steve',
        'hash' => $faker->sha256,
        'size' => rand(1, 2048),
        'likes' => rand(1, 10),
        'uploader' => factory(App\Models\User::class)->create()->uid,
        'public' => true,
        'upload_at' => $faker->dateTime,
    ];
});

$factory->state(Texture::class, 'alex', ['type' => 'alex']);

$factory->state(Texture::class, 'cape', ['type' => 'cape']);
