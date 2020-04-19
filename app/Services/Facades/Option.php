<?php

namespace App\Services\Facades;

use App\Services\OptionForm;
use Illuminate\Support\Facades\Facade;

class Option extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'options';
    }

    public static function form(string $id, string $title, $callback): OptionForm
    {
        $form = new OptionForm($id, $title);

        call_user_func($callback, $form);

        return $form;
    }
}
