<?php

namespace App\Providers;

use App\Services\TranslationLoader;
use Illuminate\Translation\TranslationServiceProvider as IlluminateTranslationServiceProvider;

class TranslationServiceProvider extends IlluminateTranslationServiceProvider
{
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new TranslationLoader($app['files'], $app['path.lang']);
        });
    }
}
