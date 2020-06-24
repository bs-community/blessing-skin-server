<?php

namespace App\Events;

class UserTryToLogin extends Event
{
    public $identification;

    public $authType;

    public function __construct($identification, $authType)
    {
        $this->identification = $identification;
        $this->authType = $authType;
    }
}
