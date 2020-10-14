<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'email' => $this->faker->email,
            'nickname' => $this->faker->name,
            'locale' => null,
            'score' => 1000,
            'avatar' => 0,
            'password' => app('cipher')->hash(Str::random(10), config('secure.salt')),
            'ip' => $this->faker->ipv4,
            'permission' => 0,
            'verified' => true,
            'last_sign_at' => $this->faker->dateTime->format('d-M-Y H:i:s'),
            'register_at' => $this->faker->dateTime->format('d-M-Y H:i:s'),
        ];
    }

    public function admin()
    {
        return $this->state(['permission' => 1]);
    }

    public function superAdmin()
    {
        return $this->state(['permission' => 2]);
    }

    public function banned()
    {
        return $this->state(['permission' => -1]);
    }
}
