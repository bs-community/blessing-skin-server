<?php

namespace App\Events;

use App\Models\User;

class UserAuthenticated extends Event
{
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  User $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
