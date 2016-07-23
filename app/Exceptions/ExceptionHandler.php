<?php

namespace App\Exceptions;

class ExceptionHandler
{
    public static function register()
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                self::handler(
                    new \ErrorException($errstr, $errno, $errno, $errfile, $errline)
                );
            });
        }
    }

    public static function handler($e)
    {
        switch ($e->getCode()) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $level = 'Fatal Error';
                break;

            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                $level = 'Warning';
                break;

            case E_NOTICE:
            case E_USER_NOTICE:
                $level = 'Notice';
                break;

            case E_STRICT:
                $level = 'Strict';
                break;

            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $level = 'Deprecated';
                break;

            default:
                $level = 'Type Unknown';
                break;
        }

        echo \View::make('errors.exception')->with('level', $level)
                                            ->with('message', $e->getMessage())
                                            ->with('file', $e->getFile())
                                            ->with('line', $e->getLine());

        exit;

    }

}
