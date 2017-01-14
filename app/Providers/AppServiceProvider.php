<?php

namespace App\Providers;

use View;
use Event;
use Utils;
use Validator;
use App\Events;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use App\Exceptions\PrettyPageException;

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

        if (option('force_ssl') || Utils::isRequestSecure()) {
            $this->app['url']->forceSchema('https');
        }

        Event::listen(Events\RenderingHeader::class, function($event) {
            // provide some application information for javascript
            $blessing = array_merge(Arr::except(config('app'), ['key', 'providers', 'aliases', 'cipher', 'log', 'url']), [
                'base_url' => url('/'),
                'site_name' => option('site_name')
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
        $className = "App\Services\Cipher\\".config('secure.cipher');

        if (class_exists($className)) {
            $this->app->singleton('cipher', $className);
        } else {
            exit(sprintf("No such encrypt method: [%s], please check your .env configuration.", config('secure.cipher')));
        }

        $this->app->singleton('users', \App\Services\Repositories\UserRepository::class);
        $this->app->singleton('parsedown', \Parsedown::class);
    }
}
