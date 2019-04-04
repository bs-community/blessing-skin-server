<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     */
    protected $dontReport = [
        HttpException::class,
        ValidationException::class,
        PrettyPageException::class,
    ];

    public function render($request, Exception $e)
    {
        if ($e instanceof ValidationException) {
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

        return parent::render($request, $e);
    }
}
