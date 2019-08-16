<?php

namespace Fake;

use Illuminate\Support\ServiceProvider;

class FakeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        event('provider.loaded');
    }
}
