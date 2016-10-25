<?php

namespace App\Events;

use Illuminate\Routing\Router;

class ConfigureWebRoutes extends Event
{
    public $router;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
}
