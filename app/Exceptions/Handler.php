<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use App\Exceptions\PrettyPageException;
use Illuminate\Session\TokenMismatchException;
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
        TokenMismatchException::class,
        ValidationException::class,
        PrettyPageException::class,
        MethodNotAllowedHttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * @param  Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Exception $e
     * @return Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            abort(403, trans('errors.http.method-not-allowed'));
        }

        if ($e instanceof TokenMismatchException) {
            return json(trans('errors.http.csrf-token-mismatch'), 1);
        }

        if ($e instanceof PrettyPageException) {
            return $e->showErrorPage();
        }

        if ($e instanceof ValidationException) {
            // Quick fix for returning 422
            // @see https://prinzeugen.net/custom-responses-of-laravel-validations/
            return $e->getResponse()->setStatusCode(200);
        }

        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return parent::render($request, $e);
            } else {
                // Hide exception details if we are not in debug mode
                if (config('app.debug') && !$request->ajax()) {
                    return $this->renderExceptionWithWhoops($e);
                } else {
                    return $this->renderExceptionInBrief($e);
                }
            }
        }
    }

    /**
     * Render the given HttpException.
     *
     * @param  \Symfony\Component\HttpKernel\Exception\HttpException  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderHttpException(HttpException $e)
    {
        $status = $e->getStatusCode();
        $message = $e->getMessage();

        // Get message from exception itself > translation > standard status texts
        if (! $message) {
            if (trans()->has($transKey = "errors.http.msg-{$status}")) {
                $message = trans($transKey);
            } else {
                $message = array_get(Response::$statusTexts, $status, "Status code: $status");
            }
        }

        if (request()->ajax()) {
            return response($message, $status, $e->getHeaders());
        }

        if (view()->exists("errors.{$status}")) {
            return response()->view("errors.{$status}", ['exception' => $e], $status, $e->getHeaders());
        }

        return response()->view('errors.http', [
            'title' => "HTTP {$status}",
            'message' => $message
        ], $status, $e->getHeaders());
    }

    /**
     * Render an exception using Whoops.
     *
     * @param  Exception $e
     * @param  int       $code
     * @param  array     $headers
     * @return Response
     */
    protected function renderExceptionWithWhoops(Exception $e, $code = 200, $headers = [])
    {
        $whoops = new \Whoops\Run;
        $handler = (request()->isMethod('GET')) ?
            new \Whoops\Handler\PrettyPageHandler : new \Whoops\Handler\PlainTextHandler;
        $whoops->pushHandler($handler);

        return response($whoops->handleException($e), $code, $headers);
    }

    /**
     * Render an exception with error messages only.
     *
     * @param  Exception $e
     * @param  int       $code
     * @param  array     $headers
     * @return Response
     */
    protected function renderExceptionInBrief(Exception $e, $code = 200, $headers = [])
    {
        if (request()->ajax()) {
            return response($e->getMessage(), $code, $headers);
        }

        return response()->view('errors.exception', [
            'message' => $e->getMessage()
        ], $code, $headers);
    }
}
