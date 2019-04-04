<?php

namespace App\Exceptions;

class PrettyPageException extends \Exception
{
    public function render()
    {
        return response()->view('errors.pretty', ['code' => $this->code, 'message' => $this->message]);
    }
}
