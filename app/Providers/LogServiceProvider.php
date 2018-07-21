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
        Log::getMonolog()->popHandler();
        Log::useFiles($this->getLogPath());

        if (! config('app.debug')) {
            $this->deleteLogs();
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

    protected function deleteLogs()
    {
        @unlink($this->getLogPath());
        @unlink(storage_path('logs/laravel.log'));
    }
}
