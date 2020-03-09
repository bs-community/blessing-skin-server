<?php

namespace Tests;

use Artisan;
use Laravel\BrowserKitTesting\TestCase;

class BrowserKitTestCase extends TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
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

    /**
     * @param \App\Models\User|string $role
     *
     * @return $this
     */
    public function actAs($role)
    {
        if (is_string($role)) {
            if ($role == 'normal') {
                $role = factory(\App\Models\User::class)->create();
            } else {
                $role = factory(\App\Models\User::class)->states($role)->create();
            }
        }

        return $this->actingAs($role);
    }
}
