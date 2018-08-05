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
        Response::macro('png', function ($src = '', $status = 200, $header = []) {
            // Handle fucking cache control
            $last_modified = Arr::pull($header, 'Last-Modified', time());
            $if_modified_since = strtotime(request()->headers->get('If-Modified-Since'));
            $if_none_match = strtotime(request()->headers->get('If-None-Match'));
            $etag = md5($src);

            // Return `304 Not Modified` if given `If-Modified-Since` header
            // is newer than our `Last-Modified` time or the `Etag` matches.
            if ($if_modified_since >= $last_modified || $if_none_match == $etag) {
                $src    = '';
                $status = 304;
            }

            return Response::make($src, $status, array_merge([
                'Content-type'  => 'image/png',
                'Last-Modified' => format_http_date($last_modified),
                'Cache-Control' => 'public, max-age='.option('cache_expire_time'),
                'Expires'       => format_http_date($last_modified + option('cache_expire_time')),
                'Etag'          => $etag
            ], $header));
        });

        Response::macro('jsonProfile', function ($src = '', $status = 200, $header = []) {
            $last_modified = Arr::pull($header, 'Last-Modified', time());
            $if_modified_since = strtotime(request()->headers->get('If-Modified-Since'));

            if ($if_modified_since && $if_modified_since >= $last_modified) {
                $src    = '';
                $status = 304;
            }

            return Response::make($src, $status, array_merge([
                'Content-type'  => 'application/json',
                'Cache-Control' => 'public, max-age='.option('cache_expire_time'),
                'Last-Modified' => format_http_date($last_modified),
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
