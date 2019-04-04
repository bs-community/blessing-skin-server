<?php

namespace App\Exceptions;

class PrettyPageException extends \Exception
{
    public function report()
    {
        return $this->render();
    }

    public function render()
    {
        return response()->view('errors.pretty', ['code' => $this->code, 'message' => $this->message]);
    }
}
