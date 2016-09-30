<?php

namespace App\Exceptions;

class PrettyPageException extends \Exception
{
    /**
     * Custom error handler
     *
     * @param string  $message
     * @param integer $code
     * @param boolean $render, to show a error page
     */
    public function __construct($message = "Error occured.", $code = -1, $render = false)
    {
        parent::__construct($message, $code);

        if ($render) {
            $this->showErrorPage()->send();
            exit;
        }
    }

    public function showErrorPage()
    {
        return response()->view('errors.pretty', ['code' => $this->code, 'message' => $this->message]);
    }
}
