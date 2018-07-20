<?php

use App\Models\User;

$factory->define(User::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->email,
        'nickname' => $faker->name,
        'score' => 1000,
        'avatar' => 0,
        'password' => app('cipher')->hash(str_random(10), config('secure.salt')),
        'ip' => '127.0.0.1',
        'permission' => 0,
        'last_sign_at' => $faker->dateTime->format('d-M-Y H:i:s'),
        'register_at' => $faker->dateTime->format('d-M-Y H:i:s')
    ];
});

$factory->defineAs(User::class, 'admin', function (Faker\Generator $faker) {
    return [
        'email' => $faker->email,
        'nickname' => $faker->name,
        'score' => 1000,
        'avatar' => 0,
        'password' => app('cipher')->hash(str_random(10), config('secure.salt')),
        'ip' => '127.0.0.1',
        'permission' => 1,
        'last_sign_at' => $faker->dateTime->format('d-M-Y H:i:s'),
        'register_at' => $faker->dateTime->format('d-M-Y H:i:s')
    ];
});

$factory->defineAs(User::class, 'superAdmin', function (Faker\Generator $faker) {
    return [
        'email' => $faker->email,
        'nickname' => $faker->name,
        'score' => 1000,
        'avatar' => 0,
        'password' => app('cipher')->hash(str_random(10), config('secure.salt')),
        'ip' => '127.0.0.1',
        'permission' => 2,
        'last_sign_at' => $faker->dateTime->format('d-M-Y H:i:s'),
        'register_at' => $faker->dateTime->format('d-M-Y H:i:s')
    ];
});

$factory->defineAs(User::class, 'banned', function (Faker\Generator $faker) {
    return [
        'email' => $faker->email,
        'nickname' => $faker->name,
        'score' => 1000,
        'avatar' => 0,
        'password' => app('cipher')->hash(str_random(10), config('secure.salt')),
        'ip' => '127.0.0.1',
        'permission' => -1,
        'last_sign_at' => $faker->dateTime->format('d-M-Y H:i:s'),
        'register_at' => $faker->dateTime->format('d-M-Y H:i:s')
    ];
});
