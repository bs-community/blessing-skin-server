<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CheckInstallationTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        $this->get('/setup')->assertSee('Already installed');

        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(storage_path('install.lock'))
                ->twice()
                ->andReturn(false);
        });
        $this->get('/setup')->assertSee(trans(
            'setup.wizard.welcome.text',
            ['version' => config('app.version')]
        ));
    }
}
