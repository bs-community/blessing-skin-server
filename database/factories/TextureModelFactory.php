<?php

use App\Models\Texture;

$factory->define(Texture::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->firstName,
        'type' => 'steve',
        'likes' => rand(0, 50),
        'hash' => $faker->sha256,
        'size' => rand(1, 2048),
        'uploader' => factory(App\Models\User::class)->create()->uid,
        'public' => true,
        'upload_at' => $faker->dateTime
    ];
});

$factory->defineAs(Texture::class, 'alex', function (Faker\Generator $faker) {
    return [
        'name' => $faker->firstName,
        'type' => 'alex',
        'likes' => rand(0, 50),
        'hash' => $faker->sha256,
        'size' => rand(1, 2048),
        'uploader' => factory(App\Models\User::class)->create()->uid,
        'public' => true,
        'upload_at' => $faker->dateTime
    ];
});

$factory->defineAs(Texture::class, 'cape', function (Faker\Generator $faker) {
    return [
        'name' => $faker->firstName,
        'type' => 'cape',
        'likes' => rand(0, 50),
        'hash' => $faker->sha256,
        'size' => rand(1, 2048),
        'uploader' => factory(App\Models\User::class)->create()->uid,
        'public' => true,
        'upload_at' => $faker->dateTime
    ];
});
