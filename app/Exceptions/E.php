<?php

namespace App\Exceptions;

class E extends \Exception
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
        if ($render) {
            $this->showErrorPage();
        } else {
            $this->showErrorJson();
        }
    }

    private function showErrorJson()
    {
        $exception['errno'] = $this->code;
        $exception['msg'] = $this->message;
        @header('Content-type: application/json; charset=utf-8');
        exit(json_encode($exception));
    }

    private function showErrorPage()
    {
        echo \View::make('errors.e')->with('code', $this->code)->with('message', $this->message);
        exit;
    }
}
