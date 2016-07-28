<?php

namespace App\Services;

use Illuminate\Database\Capsule\Manager as Capsule;

class Schema
{
    /**
     * Facade for Illuminate\Database\Schema
     *
     * @param  string  $method
     * @param  array   $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        // the instance of capusle has been set as global
        $instance = Capsule::schema();

        return call_user_func_array([$instance, $method], $args);
    }

}
