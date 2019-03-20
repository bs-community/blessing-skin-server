<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull as Converter;

class ConvertEmptyStringsToNull extends Converter
{
    protected $excepts = [
        'admin/options',
        'admin/score',
        'admin/resource',
    ];

    public function handle($request, Closure $next)
    {
        if (in_array($request->path(), $this->excepts)) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
