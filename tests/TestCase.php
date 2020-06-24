<?php

namespace Tests;

use Artisan;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     */
    protected $baseUrl = 'http://localhost';

    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        Artisan::call('migrate:refresh');

        if (!file_exists(storage_path('install.lock'))) {
            file_put_contents(storage_path('install.lock'), '');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->spy(\App\Services\Translations\JavaScript::class);
    }
}
