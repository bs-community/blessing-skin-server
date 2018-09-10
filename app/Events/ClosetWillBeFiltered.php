<?php

namespace App\Events;

use App\Models\Closet;

class ClosetWillBeFiltered extends Event
{
    public $closet;

    public function __construct(Closet $closet)
    {
        $this->closet = $closet;
    }
}
