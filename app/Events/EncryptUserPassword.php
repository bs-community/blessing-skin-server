<?php

namespace App\Events;

use App\Models\User;

class EncryptUserPassword extends Event
{
    public $user;

    public $rawPasswd;

    /**
     * Create a new event instance.
     *
     * @param  string $rawPasswd
     * @param  User   $user
     * @return void
     */
    public function __construct($rawPasswd, User $user)
    {
        $this->rawPasswd = $rawPasswd;
        $this->user = $user;
    }
}
