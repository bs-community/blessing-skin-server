<?php

namespace Tests;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class OptionsCacheCommandTest extends TestCase
{
    public function testRun()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')->andReturn(false);
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
