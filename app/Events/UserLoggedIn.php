<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class UserLoggedIn extends Event
{
    use SerializesModels;

    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(\App\Models\User $user)
    {
        $this->user = $user;
    }

}
