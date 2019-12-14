<?php

namespace Tests;

use App\Services\PackageManager;
use Cache;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;
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

    public function testProgress()
    {
        $package = resolve(PackageManager::class);
        $reflect = new ReflectionClass($package);
        $property = $reflect->getProperty('cacheKey');
        $property->setAccessible(true);
        $property->setValue($package, 'key');

        Cache::put('key', serialize(['total' => 0, 'done' => 0]));
        $this->assertEquals(0, $package->progress());

        Cache::put('key', serialize(['total' => 2, 'done' => 1]));
        $this->assertEquals(0.5, $package->progress());
    }

    public function testOnProgress()
    {
        $package = resolve(PackageManager::class);
        $reflect = new ReflectionClass($package);
        $property = $reflect->getProperty('cacheKey');
        $property->setAccessible(true);
        $property->setValue($package, 'key');
        $closure = $reflect->getProperty('onProgress');
        $closure->setAccessible(true);

        Cache::shouldReceive('put')->with('key', serialize(['total' => 5, 'done' => 4]));
        call_user_func($closure->getValue($package), 5, 4);
    }
}
