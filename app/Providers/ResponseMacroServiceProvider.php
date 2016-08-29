<?php

namespace App\Providers;

use Response;
use Illuminate\Support\ServiceProvider;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @param  ResponseFactory  $factory
     * @return void
     */
    public function boot()
    {
        Response::macro('png', function ($src = "", $status = 200, $header = []) {
            return Response::stream(function() use ($src, $status) {
                echo $src;
            }, $status, array_merge([
                'Content-type' => 'image/png',
            ], $header));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
