<?php

namespace Tests;

use Cache;
use Exception;
use ZipArchive;
use ReflectionClass;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use App\Services\PackageManager;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Exception\RequestException;

class PackageManagerTest extends TestCase
{
    public function testDownload()
    {
        $mock = new MockHandler([
            new Response(200, [], 'contents'),
            new Response(200, [], 'contents'),
            new RequestException('error', new Request('GET', 'url')),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $package = new PackageManager($client);
        $this->assertInstanceOf(
            PackageManager::class,
            $package->download('url', storage_path('packages/temp'))
        );

        $this->expectException(Exception::class);
        $package->download('url', storage_path('packages/temp'), 'deadbeef');

        $this->expectException(Exception::class);
        $package->download('url', storage_path('packages/temp'));
    }

    public function testExtract()
    {
        $mock = new MockHandler([new Response(200, [], 'contents')]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $package = new PackageManager($client);
        $path = storage_path('packages/temp.zip');

        $package->download('url', $path);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path, ZipArchive::OVERWRITE));
        $this->assertTrue($zip->addEmptyDir('zip-test'));
        $zip->close();
        $package->extract(storage_path('testing'));

        $this->expectException(Exception::class);
        $package->download('url', $path)->extract(storage_path('testing'));
    }

    public function testProgress()
    {
        $package = new PackageManager(new Client());
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
        $package = new PackageManager(new Client());
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
