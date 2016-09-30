<?php

namespace App\Exceptions;

use Exception;
use App\Exceptions\PrettyPageException;
use App\Http\Middleware\Internationalization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        PrettyPageException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($e instanceof PrettyPageException && PHP_SAPI != "cli") {
            return $e->showErrorPage();
        }

        if ($e instanceof HttpException) {
            // call i18n middleware manually since http exceptions won't be sent through it
            (new Internationalization)->handle($request, function(){});
        }

        if ($e instanceof ValidationException) {
            return $e->getResponse()->setStatusCode(200);
        }

        if (config('app.debug')) {
            foreach ($this->dontReport as $type) {
                if ($e instanceof $type) {
                    return parent::render($request, $e);
                }
            }
            return $this->renderExceptionWithWhoops($e);
        }

        return parent::render($request, $e);
    }

    /**
     * Render an exception using Whoops.
     *
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    protected function renderExceptionWithWhoops(Exception $e)
    {
        $whoops = new \Whoops\Run;
        $handler = ($_SERVER['REQUEST_METHOD'] == "GET") ?
                        new \Whoops\Handler\PrettyPageHandler : new \Whoops\Handler\PlainTextHandler;
        $whoops->pushHandler($handler);

        return new \Illuminate\Http\Response(
            $whoops->handleException($e),
            $e->getStatusCode(),
            $e->getHeaders()
        );
    }
}
