<?php

namespace App\Providers;

use Log;
use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Log::channel('single')->popHandler();
        config(['logging.channels.single.path' => $this->getLogPath()]);

        if (! config('app.debug')) {
            @unlink(storage_path('logs/laravel.log'));
        }
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

    protected static function getLogPath()
    {
        $mask = substr(md5(implode(',', array_values(get_db_config()))), 0, 16);

        return storage_path("logs/bs-$mask.log");
    }
}
