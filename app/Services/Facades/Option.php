<?php

namespace App\Services\Facades;

use App\Services\OptionForm;
use Illuminate\Support\Facades\Facade;

class Option extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'options';
    }

    public static function form($id, $title, $callback)
    {
        $form = new OptionForm($id, $title);

        call_user_func($callback, $form);

        return $form;
    }
}
