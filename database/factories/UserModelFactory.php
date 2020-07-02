<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\User;
use Illuminate\Support\Str;

$factory->define(User::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->email,
        'nickname' => $faker->name,
        'locale' => null,
        'score' => 1000,
        'avatar' => 0,
        'password' => app('cipher')->hash(Str::random(10), config('secure.salt')),
        'ip' => '127.0.0.1',
        'permission' => 0,
        'verified' => true,
        'last_sign_at' => $faker->dateTime->format('d-M-Y H:i:s'),
        'register_at' => $faker->dateTime->format('d-M-Y H:i:s'),
    ];
});

$factory->state(User::class, 'admin', ['permission' => 1]);

$factory->state(User::class, 'superAdmin', ['permission' => 2]);

$factory->state(User::class, 'banned', ['permission' => -1]);
