<?php

namespace Tests;

use Symfony\Component\Finder\SplFileInfo;

class CleanUpFrontEndLocaleFilesTest extends TestCase
{
    public function testHandle()
    {
        $plugin = new \App\Services\Plugin('/path', ['name' => 'fake']);

        $this->partialMock(\Illuminate\Filesystem\Filesystem::class, function ($mock) {
            $dir = public_path('lang');
            $path = public_path('lang/en.js');
            $mock->shouldReceive('allFiles')
                ->with($dir)
                ->once()
                ->andReturn([new SplFileInfo(public_path('lang/en.js'), 'en.js', 'en.js')]);
            $mock->shouldReceive('delete')
                ->with($path)
                ->once();
        });

        event('plugin.versionChanged', [$plugin]);
    }
}
