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
    function __construct($message = "Error occured.", $code = -1, $render = false)
    {
        parent::__construct($message, $code);

        if ($render)
            $this->showErrorPage();
    }

    private function showErrorPage()
    {
        echo \View::make('errors.e')->with('code', $this->code)
                                    ->with('message', $this->message)
                                    ->render();
        exit;
    }
}
