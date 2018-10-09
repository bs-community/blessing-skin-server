<?php

namespace App\Events;

use App\Models\User;

class EncryptUserPassword extends Event
{
    public $user;

    public $rawPassword;

    /**
     * Create a new event instance.
     *
     * @param  string $rawPassword
     * @param  User   $user
     * @return void
     */
    public function __construct($rawPassword, User $user)
    {
        $this->rawPassword = $rawPassword;
        $this->user = $user;
    }
}
