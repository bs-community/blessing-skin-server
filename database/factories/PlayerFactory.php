<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition()
    {
        return [
            'uid' => User::factory(),
            'name' => $this->faker->firstName,
            'tid_skin' => 0,
        ];
    }
}
