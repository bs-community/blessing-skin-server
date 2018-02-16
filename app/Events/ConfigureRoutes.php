<?php

namespace App\Events;

use Illuminate\Routing\Router;

class ConfigureRoutes extends Event
{
    public $router;

    /**
     * Create a new event instance.
     *
     * @param  Router $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
}
