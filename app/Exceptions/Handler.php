<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Validation\ValidationException::class,
        PrettyPageException::class,
    ];

    protected function convertExceptionToArray(Exception $e)
    {
        return [
            'message' => $e->getMessage(),
            'exception' => true,
            'trace' => collect($e->getTrace())
                ->map(function ($trace) {
                    return Arr::only($trace, ['file', 'line']);
                })
                ->filter(function ($trace) {
                    return Arr::has($trace, 'file');
                })
                ->map(function ($trace) {
                    $trace['file'] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $trace['file']);

                    return $trace;
                })
                ->filter(function ($trace) {
                    return Str::startsWith($trace['file'], 'app') ||
                        Str::contains($trace['file'], resolve('plugins')->getPluginsDirs()->all());
                })
                ->values(),
        ];
    }
}
