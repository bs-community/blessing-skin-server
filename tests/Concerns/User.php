<?php

namespace Tests\Concerns;

use App\Models\User as Model;

class User extends Model
{
    protected static $mappings = [
        'email' => 'bs_email',
        'nickname' => 'bs_nickname',
        'score' => 'bs_score',
        'avatar' => 'bs_avatar',
        'password' => 'bs_password',
        'ip' => 'bs_ip',
        'permission' => 'bs_permission',
        'last_sign_at' => 'bs_last_sign_at',
        'register_at' => 'bs_register_at',
        'verified' => 'bs_verified',
        'remember_token' => 'bs_remember_token',
    ];
}
