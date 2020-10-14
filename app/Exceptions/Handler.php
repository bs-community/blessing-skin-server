<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Validation\ValidationException::class,
        \Illuminate\Session\TokenMismatchException::class,
        ModelNotFoundException::class,
        PrettyPageException::class,
    ];

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            $model = $exception->getModel();
            if (Str::endsWith($model, 'Texture')) {
                $exception = new ModelNotFoundException(trans('skinlib.non-existent'));
            }
        }

        return parent::render($request, $exception);
    }

    protected function convertExceptionToArray(Throwable $e)
    {
        return [
            'message' => $e->getMessage(),
            'exception' => true,
            'trace' => collect($e->getTrace())
                ->map(fn ($trace) => Arr::only($trace, ['file', 'line']))
                ->filter(fn ($trace) => Arr::has($trace, 'file'))
                ->map(function ($trace) {
                    $trace['file'] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $trace['file']);

                    return $trace;
                })
                ->filter(function ($trace) {
                    // @codeCoverageIgnoreStart
                    $isFromPlugins = !app()->runningUnitTests() &&
                        Str::contains($trace['file'], resolve('plugins')->getPluginsDirs()->all());
                    // @codeCoverageIgnoreEnd
                    return Str::startsWith($trace['file'], 'app') || $isFromPlugins;
                })
                ->values(),
        ];
    }
}
