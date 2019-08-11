<?php

namespace Tests;

use ReflectionClass;
use App\Services\PluginManager;
use Illuminate\Filesystem\Filesystem;

class PluginManagerTest extends TestCase
{
    public function rebootPluginManager(PluginManager $manager)
    {
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('booted');
        $property->setAccessible(true);
        $property->setValue($manager, false);

        $manager->boot();
        return $manager;
    }

    public function testPreventBootAgain()
    {
        // TODO: modify asserting 0 times here
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')->times(1);
        });
        app('plugins')->boot();
        app('plugins')->boot();
    }

    public function testRegisterAutoload()
    {
        config(['plugins.directory' => storage_path('mocks')]);
        $this->assertFalse(class_exists('Fake\Faker'));
        $manager = $this->rebootPluginManager(app('plugins'));
        $this->assertTrue(class_exists('Fake\Faker'));

        config(['plugins.directory' => env('PLUGINS_DIR')]);
    }

    public function testReportDuplicatedPlugins()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')
                ->with(config('plugins.directory'))
                ->once()
                ->andReturn(collect(['/nano', '/yuko']));

            $mock->shouldReceive('exists')
                ->with('/nano/package.json')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with('/nano/package.json')
                ->once()
                ->andReturn(json_encode([
                    'name' => 'fake',
                    'version' => '0.0.0',
                ]));

            $mock->shouldReceive('exists')
                ->with('/yuko/package.json')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with('/yuko/package.json')
                ->once()
                ->andReturn(json_encode([
                    'name' => 'fake',
                    'version' => '0.0.0',
                ]));
        });

        $this->expectExceptionMessage(trans('errors.plugins.duplicate', [
            'dir1' => '/nano',
            'dir2' => '/yuko',
        ]));
        $manager = $this->rebootPluginManager(app('plugins'));
    }
}
