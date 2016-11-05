<?php

namespace App\Events;

use App\Models\User;

class EncryptUserPassword extends Event
{
    public $rawPasswd;

    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($raw_passwd, User $user)
    {
        $this->rawPasswd = $raw_passwd;
        $this->user = $user;
    }

}
