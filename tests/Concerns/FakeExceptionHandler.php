<?php

/**
 * @see TestCase::disableExceptionHandling
 */
class FakeExceptionHandler extends App\Exceptions\Handler
{
    public function __construct() {
        //
    }

    public function report(Exception $e) {
        //
    }

    public function render($request, Exception $e)
    {
        // Just re-throw the exception
        throw $e;
    }
}
