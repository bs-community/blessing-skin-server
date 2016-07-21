<?php

namespace App\Exceptions;

use Pecee\Http\Request;
use Pecee\SimpleRouter\RouterEntry;
use Pecee\Handler\IExceptionHandler;

class RouterExceptionHandler implements IExceptionHandler
{

    public function handleError(Request $request, RouterEntry $router = null, \Exception $error)
    {
        if ($error->getCode() === 404) {
            \Http::abort(404, $error->getMessage(), ($_SERVER['REQUEST_METHOD'] == "POST"));
        } else {
            throw $error;
        }

    }

}
