<?php

namespace App\Events;

use App\Models\User;

class UserProfileUpdated extends Event
{
    public $type;
    public $user;

    public function __construct($type, User $user)
    {
        $this->type = $type;
        $this->user = $user;
    }
}
