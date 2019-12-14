<?php

namespace App\Events;

class UserTryToLogin extends Event
{
    public $identification;

    public $authType;

    /**
     * Create a new event instance.
     *
     * @param string $identification email or username of the user
     * @param string $authType       "email" or "username"
     *
     * @return void
     */
    public function __construct($identification, $authType)
    {
        $this->identification = $identification;
        $this->authType = $authType;
    }
}
