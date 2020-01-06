<?php

namespace Tests;

use App\Services\PackageManager;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Filesystem\Filesystem;
use ZipArchive;

class PackageManagerTest extends TestCase
{
    public function testDownload()
    {
        $mock = new MockHandler([
            new Response(200, [], 'contents'),
            new RequestException('error', new Request('GET', 'url')),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->instance(Client::class, $client);

        $package = resolve(PackageManager::class);
        $this->assertInstanceOf(
            PackageManager::class,
            $package->download('url', storage_path('packages/temp'))
        );

        $this->expectExceptionMessage(trans('admin.download.errors.download', ['error' => 'error']));
        $package->download('url', storage_path('packages/temp'));
    }

    public function testShasumCheck()
    {
        $mock = new MockHandler([new Response(200, [], 'contents')]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->instance(Client::class, $client);

        $package = resolve(PackageManager::class);
        $this->expectExceptionMessage(trans('admin.download.errors.shasum'));
        $package->download('url', storage_path('packages/temp'), 'deadbeef');
    }

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
        $package = resolve(PackageManager::class);

        // The call below is expected success.
        $package->extract('dest');

        $this->expectException(Exception::class);
        $package->extract('dest');
    }
}
