<?php

namespace App\Events;

use App\Models\User;

class UserProfileUpdated extends Event
{
    public $type;
    public $user;

    /**
     * Create a new event instance.
     *
     * @param string $type which type of user profile was updated
     *
     * @return void
     */
    public function __construct($type, User $user)
    {
        $this->type = $type;
        $this->user = $user;
    }
}
