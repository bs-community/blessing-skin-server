<?php

namespace App\Providers;

use View;
use Blade;
use Event;
use App\Events;
use App\Models\User;
use ReflectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use App\Exceptions\PrettyPageException;
use App\Services\Repositories\OptionRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Control the URL generated by url() function
        $this->configureUrlGenerator();

        Blade::if('admin', function (User $user) {
            return $user->isAdmin();
        });

        Event::listen(Events\RenderingHeader::class, function($event) {
            // Provide some application information for javascript
            $blessing = array_merge(Arr::except(config('app'), ['key', 'providers', 'aliases', 'cipher', 'log', 'url']), [
                'base_url' => url('/'),
                'site_name' => option_localized('site_name'),
                'route' => request()->path(),
            ]);

            $event->addContent('<script>var blessing = '.json_encode($blessing).';</script>');
        });

        try {
            $this->app->make('cipher');
        } catch (ReflectionException $e) {
            throw new PrettyPageException(trans('errors.cipher.unsupported', ['cipher' => config('secure.cipher')]));
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cipher', 'App\Services\Cipher\\'.config('secure.cipher'));
        $this->app->singleton('users', \App\Services\Repositories\UserRepository::class);
        $this->app->singleton('options',  OptionRepository::class);

        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        // Support *.tpl extension name
        View::addExtension('tpl', 'blade');
        // Make the priority of *.blade.php higher than *.tpl
        View::addExtension('blade.php', 'blade');

        $this->app->singleton('parsedown', \Parsedown::class);
    }

    /**
     * Configure the \Illuminate\Routing\UrlGenerator.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    protected function configureUrlGenerator()
    {
        if (! option('auto_detect_asset_url')) {
            $rootUrl = option('site_url');

            // Replace HTTP_HOST with site_url set in options,
            // to prevent CDN source problems.
            if ($this->app['url']->isValidUrl($rootUrl)) {
                $this->app['url']->forceRootUrl($rootUrl);
            }
        }

        if (option('force_ssl') || is_request_secure()) {
            $this->app['url']->forceScheme('https');
        }
    }
}
