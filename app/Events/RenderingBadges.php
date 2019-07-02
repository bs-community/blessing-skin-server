<?php

namespace App\Events;

class RenderingBadges extends Event
{
    public $badges;

    public function __construct(array &$badges)
    {
        $this->badges = &$badges;
    }
}
