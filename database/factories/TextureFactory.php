<?php

namespace Database\Factories;

use App\Models\Texture;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TextureFactory extends Factory
{
    protected $model = Texture::class;

    public function definition()
    {
        return [
            'name' => $this->faker->firstName,
            'type' => 'steve',
            'hash' => $this->faker->sha256,
            'size' => rand(1, 2048),
            'likes' => rand(1, 10),
            'uploader' => User::factory(),
            'public' => true,
            'upload_at' => $this->faker->dateTime,
        ];
    }

    public function alex()
    {
        return $this->state(['type' => 'alex']);
    }

    public function cape()
    {
        return $this->state(['type' => 'cape']);
    }

    public function private()
    {
        return $this->state(['public' => false]);
    }
}
