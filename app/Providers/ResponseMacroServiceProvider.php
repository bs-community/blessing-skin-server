<?php

namespace App\Providers;

use Response;
use Illuminate\Support\Arr;
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
            $last_modified = Arr::pull($header, 'Last-Modified', time());
            $etag = md5($src);

            // Checking if the client is validating his cache and if it is current.
            if ((strtotime(Arr::get($_SERVER, 'If-Modified-Since')) == $last_modified) ||
                    trim(Arr::get($_SERVER, 'HTTP_IF_NONE_MATCH')) == $etag
            ) {
                // Client's cache IS current, so we just respond '304 Not Modified'.
                $status = 304;
                $src    = "";
            }

            return Response::stream(function() use ($src, $status) {
                echo $src;
            }, $status, array_merge([
                'Content-type'  => 'image/png',
                'Last-Modified' => gmdate('D, d M Y H:i:s', $last_modified).' GMT',
                'Cache-Control' => 'public, max-age=31536000', // 365 days
                'Expires'       => gmdate('D, d M Y H:i:s', $last_modified + 31536000).' GMT',
                'Etag'          => $etag
            ], $header));
        });

        Response::macro('rawJson', function ($src = "", $status = 200, $header = []) {
            return Response::make($src)->header('Content-type', 'application/json');
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
