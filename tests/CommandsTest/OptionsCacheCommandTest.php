<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class OptionsCacheCommandTest extends TestCase
{
    public function testRun()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')->andReturn(false);
            $mock->shouldReceive('delete')->with(storage_path('options/cache.php'))->once();
            $mock->shouldReceive('put')
                ->withArgs(function ($path, $content) {
                    $this->assertEquals(storage_path('options/cache.php'), $path);
                    $this->assertTrue(Str::startsWith($content, '<?php'.PHP_EOL.'return'));
                    $this->assertTrue(Str::endsWith($content, ';'));

                    return true;
                })
                ->once();
        });

        $this->artisan('options:cache')->expectsOutput('Options cached successfully.');
    }
}
