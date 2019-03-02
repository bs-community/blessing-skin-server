<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
            if ($request->expectsJson()) {
                return json(trans('errors.http.csrf-token-mismatch'), 1);
            }
            abort(403, trans('errors.http.csrf-token-mismatch'));
        }

        if ($e instanceof PrettyPageException) {
            return $e->showErrorPage();
        }

        if ($e instanceof ValidationException) {
            // Quick fix for returning 422
            // @see https://prinzeugen.net/custom-responses-of-laravel-validations/
            if ($request->expectsJson()) {
                return response()->json([
                    'errno' => 1,
                    'msg' => $e->validator->errors()->first(),
                ]);
            } else {
                $request->session()->flash('errors', $e->validator->errors());

                return redirect()->back();
            }
        }

        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return parent::render($request, $e);
            } else {
                // Hide exception details if we are not in debug mode
                if (config('app.debug') && ! $request->ajax()) {
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

        return new Response(
            $whoops->handleException($e),
            $code,
            $headers
        );
    }

    /**
     * Render an exception in a short word.
     *
     * @param  Exception $e
     * @return Response
     */
    protected function renderExceptionInBrief(Exception $e)
    {
        if (request()->isMethod('GET') && ! request()->ajax()) {
            return response()->view('errors.exception', ['message' => $e->getMessage()]);
        } else {
            return response($e->getMessage());
        }
    }
}
