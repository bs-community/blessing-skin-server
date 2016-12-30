<?php

namespace App\Providers;

use View;
use Event;
use Validator;
use App\Events;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // replace HTTP_HOST with site url setted in options to prevent CDN source problems
        if (!option('auto_detect_asset_url')) {
            $rootUrl = option('site_url');

            if ($this->app['url']->isValidUrl($rootUrl)) {
                $this->app['url']->forceRootUrl($rootUrl);
            }
        }

        if (option('force_ssl')) {
            $this->app['url']->forceSchema('https');
        }

        Event::listen(Events\RenderingHeader::class, function($event) {
            // provide some application information for javascript
            $blessing = array_merge(Arr::except(config('app'), ['key', 'providers', 'aliases', 'cipher', 'log', 'url']), [
                'baseUrl' => url('/'),
            ]);

            $event->addContent('<script>var blessing = '.json_encode($blessing).';</script>');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // register default cipher
        $this->app->singleton('cipher', "App\Services\Cipher\\".config('secure.cipher'));
        $this->app->singleton('users', \App\Services\Repositories\UserRepository::class);
        $this->app->singleton('parsedown', \Parsedown::class);
    }
}
