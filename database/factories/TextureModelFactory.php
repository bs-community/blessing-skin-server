<?php

use App\Models\Texture;

$factory->define(Texture::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->firstName,
        'type' => 'steve',
        'hash' => $faker->sha256,
        'size' => rand(1, 2048),
        'uploader' => factory(App\Models\User::class)->create()->uid,
        'public' => true,
        'upload_at' => $faker->dateTime,
    ];
});

$factory->defineAs(Texture::class, 'alex', function (Faker\Generator $faker) {
    return [
        'name' => $faker->firstName,
        'type' => 'alex',
        'hash' => $faker->sha256,
        'size' => rand(1, 2048),
        'uploader' => factory(App\Models\User::class)->create()->uid,
        'public' => true,
        'upload_at' => $faker->dateTime,
    ];
});

$factory->defineAs(Texture::class, 'cape', function (Faker\Generator $faker) {
    return [
        'name' => $faker->firstName,
        'type' => 'cape',
        'hash' => $faker->sha256,
        'size' => rand(1, 2048),
        'uploader' => factory(App\Models\User::class)->create()->uid,
        'public' => true,
        'upload_at' => $faker->dateTime,
    ];
});
