<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class UserInstantiated extends Event
{
    use SerializesModels;

    public $uid;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($uid)
    {
        $this->uid = $uid;
    }

}
