<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Laravel\BrowserKitTesting\TestCase;

class BrowserKitTestCase extends TestCase
{
    /**
     * The base URL to use while testing the application.
     */
    protected $baseUrl = 'http://localhost';

    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        Artisan::call('migrate:refresh');

        if (!file_exists(storage_path('install.lock'))) {
            file_put_contents(storage_path('install.lock'), '');
        }

        return $app;
    }
}
