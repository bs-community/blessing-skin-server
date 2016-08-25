<?php

namespace Blessing\Facades;

use \Illuminate\Support\Facades\Facade;

/**
 * @see \Blessing\Foundation\Application
 */
class App extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'app';
    }
}
