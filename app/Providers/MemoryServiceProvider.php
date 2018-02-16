<?php

namespace App\Providers;

use Storage;
use League\Flysystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Memory\MemoryAdapter;

class MemoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Storage::extend('memory', function ($app, $config) {
            return new Filesystem(new MemoryAdapter());
        });
    }

    public function register()
    {
        //
    }
}
