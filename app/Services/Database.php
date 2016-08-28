<?php

namespace App\Services;

use \Illuminate\Support\Facades\Facade;

class Database extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'database';
    }
}
