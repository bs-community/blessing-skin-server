<?php

namespace App\Events;

use Illuminate\Routing\Router;

class ConfigureRoutes extends Event
{
    public $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }
}
