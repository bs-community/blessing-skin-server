<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class UserTryToLogin extends Event
{
    use SerializesModels;

    public $identification;

    public $auth_type;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($identification, $auth_type)
    {
        $this->identification = $identification;
        $this->auth_type = $auth_type;
    }

}
