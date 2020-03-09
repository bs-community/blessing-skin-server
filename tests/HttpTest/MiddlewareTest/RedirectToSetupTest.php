<?php

namespace Tests;

use App\Models\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RedirectToSetupTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        $superAdmin = factory(User::class)->states('superAdmin')->create();

        $current = config('app.version');
        config(['app.version' => '100.0.0']);
        $this->get('/')->assertStatus(503);
        $this->actingAs($superAdmin)->get('/')->assertRedirect('/setup/update');
        config(['app.version' => $current]);

        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(storage_path('install.lock'))
                ->andReturn(true, false, false);

            $mock->shouldReceive('exists')
                ->with(base_path('.env'))
                ->andReturn(true);
        });
        $this->get('/')->assertViewIs('home');
        $this->get('/setup')->assertViewIs('setup.wizard.welcome');
        $this->get('/')->assertRedirect('/setup');
        $this->assertEquals([], config('translation-loader.translation_loaders'));
    }
}
