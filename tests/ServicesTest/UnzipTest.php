<?php

namespace Tests;

use App\Services\Unzip;
use Exception;
use Illuminate\Filesystem\Filesystem;
use ZipArchive;

class UnzipTest extends TestCase
{
    public function testExtract()
    {
        $this->mock(ZipArchive::class, function ($mock) {
            $mock->shouldReceive('open')
                ->twice()
                ->andReturn(true, false);

            $mock->shouldReceive('extractTo')
                ->with('dest')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('close')->once();
        });
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('delete')->once();
        });
        /** @var Unzip */
        $unzip = resolve(Unzip::class);

        // The call below is expected success.
        $unzip->extract('f.zip', 'dest');

        $this->expectException(Exception::class);
        $unzip->extract('f.zip', 'dest');
    }
}
