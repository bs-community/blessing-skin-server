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
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

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
        PrettyPageException::class,
        MethodNotAllowedHttpException::class,
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
        // call i18n middleware manually since http exceptions won't be sent through it
        (new Internationalization)->handle($request, function(){});

        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            abort(403, 'Method not allowed.');
        }

        if ($e instanceof PrettyPageException && PHP_SAPI != "cli") {
            return $e->showErrorPage();
        }

        if ($e instanceof ValidationException) {
            // quick fix for returning 422
            // @see https://prinzeugen.net/custom-responses-of-laravel-validations/
            return $e->getResponse()->setStatusCode(200);
        }

        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return parent::render($request, $e);
            } else {
                // hide exception details if not in debug mode
                if (config('app.debug')) {
                    return $this->renderExceptionWithWhoops($e);
                } else {
                    return $this->renderExceptionInBrief($e);
                }
            }
        }
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

    /**
     * Render an exception in a short word.
     *
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    protected function renderExceptionInBrief(Exception $e)
    {
        return response()->view('errors.brief');
    }
}
