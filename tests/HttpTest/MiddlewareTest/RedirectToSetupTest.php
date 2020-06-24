<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;

class RedirectToSetupTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        $current = config('app.version');
        config(['app.version' => '100.0.0']);
        Artisan::shouldReceive('call')->with('update')->once();
        $this->get('/');
        config(['app.version' => $current]);

        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(storage_path('install.lock'))
                ->andReturn(true, false, false);
        });
        $this->get('/')->assertViewIs('home');
        $this->get('/setup')->assertViewIs('setup.wizard.welcome');
        $this->get('/')->assertRedirect('/setup');
        $this->assertEquals([], config('translation-loader.translation_loaders'));
    }
}
