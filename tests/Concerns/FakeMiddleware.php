<?php

namespace Tests\Concerns;

class FakeMiddleware
{
    public function handle($request, $next)
    {
        $response = $next($request);
        $response->header('X-Middleware-Test', 'value');

        return $response;
    }
}
