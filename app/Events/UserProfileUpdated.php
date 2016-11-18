<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

class UserProfileUpdated extends Event
{
    use SerializesModels;

    public $type;
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, User $user)
    {
        $this->type = $type;
        $this->user = $user;
    }

}
