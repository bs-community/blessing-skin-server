<?php

namespace Tests;

use Event;
use App\Events;
use ReflectionClass;
use App\Services\Plugin;
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

    public function testPreventBootingAgain()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')->times(1);
        });
        app('plugins')->boot();
        app('plugins')->boot();
    }

    public function testNotLoadDisabled()
    {
        $dir = config('plugins.directory');
        config(['plugins.directory' => storage_path('mocks')]);

        $manager = $this->rebootPluginManager(app('plugins'));
        $this->assertFalse(class_exists('Fake\Faker'));

        config(['plugins.directory' => $dir]);
    }

    public function testNotLoadUnsatisfied()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')
                ->with(base_path('plugins'))
                ->once()
                ->andReturn(collect(['/nano']));

            $mock->shouldReceive('exists')
                ->with('/nano'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with('/nano'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(json_encode([
                    'name' => 'fake',
                    'version' => '0.0.0',
                    'require' => ['blessing-skin-server' => '0.0.0'],
                ]));

            $mock->shouldNotReceive('getRequire');
        });

        $manager = $this->rebootPluginManager(app('plugins'));
    }

    public function testReportDuplicatedPlugins()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')
                ->with(base_path('plugins'))
                ->once()
                ->andReturn(collect(['/nano', '/yuko']));

            $mock->shouldReceive('exists')
                ->with('/nano'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with('/nano'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(json_encode([
                    'name' => 'fake',
                    'version' => '0.0.0',
                ]));

            $mock->shouldReceive('exists')
                ->with('/yuko'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with('/yuko'.DIRECTORY_SEPARATOR.'package.json')
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

    public function testDetectVersionChanged()
    {
        option(['plugins_enabled' => json_encode([['name' => 'mayaka', 'version' => '0.0.0']])]);
        Event::fake();
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')
                ->with(base_path('plugins'))
                ->once()
                ->andReturn(collect(['/mayaka']));

            $mock->shouldReceive('exists')
                ->with('/mayaka'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with('/mayaka'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(json_encode([
                    'name' => 'mayaka',
                    'version' => '0.1.0',
                ]));

            $mock->shouldReceive('exists')
                ->with('/mayaka/vendor/autoload.php')
                ->once()
                ->andReturn(false);
            $mock->shouldReceive('exists')
                ->with('/mayaka/bootstrap.php')
                ->once()
                ->andReturn(false);
        });

        $manager = $this->rebootPluginManager(app('plugins'));
        Event::assertDispatched(\App\Events\PluginVersionChanged::class, function ($event) {
            $this->assertEquals('0.1.0', $event->plugin->version);

            return true;
        });

        option(['plugins_enabled' => '[]']);
    }

    public function testLoadComposer()
    {
        option(['plugins_enabled' => json_encode([['name' => 'mayaka', 'version' => '0.0.0']])]);
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')
                ->with(base_path('plugins'))
                ->once()
                ->andReturn(collect(['/mayaka']));

            $mock->shouldReceive('exists')
                ->with('/mayaka'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with('/mayaka'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(json_encode([
                    'name' => 'mayaka',
                    'version' => '0.0.0',
                ]));

            $mock->shouldReceive('exists')
                ->with('/mayaka/vendor/autoload.php')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('getRequire')
                ->with('/mayaka/vendor/autoload.php')
                ->once();

            $mock->shouldReceive('exists')
                ->with('/mayaka/bootstrap.php')
                ->once()
                ->andReturn(false);
        });

        $manager = $this->rebootPluginManager(app('plugins'));

        option(['plugins_enabled' => '[]']);
    }

    public function testLoadViewsAndTranslations()
    {
        option(['plugins_enabled' => json_encode([['name' => 'mayaka', 'version' => '0.0.0']])]);
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')
                ->with(base_path('plugins'))
                ->once()
                ->andReturn(collect(['/mayaka']));

            $mock->shouldReceive('exists')
                ->with('/mayaka'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with('/mayaka'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(json_encode([
                    'name' => 'mayaka',
                    'version' => '0.0.0',
                    'namespace' => 'Mayaka',
                ]));

            $mock->shouldReceive('exists')
                ->with('/mayaka/vendor/autoload.php')
                ->once()
                ->andReturn(false);
            $mock->shouldReceive('exists')
                ->with('/mayaka/bootstrap.php')
                ->once()
                ->andReturn(false);
        });
        $this->mock('view', function ($mock) {
            $mock->shouldReceive('addNamespace')
                ->withArgs(['Mayaka', '/mayaka/views'])
                ->once();
        });
        $this->instance('translation.loader', \Mockery::mock(\App\Services\TranslationLoader::class, function ($mock) {
            $mock->shouldReceive('addNamespace')
                ->withArgs(['Mayaka', '/mayaka/lang'])
                ->once();
        }));

        $manager = $this->rebootPluginManager(app('plugins'));

        option(['plugins_enabled' => '[]']);
    }

    public function testLoadBootstrapper()
    {
        option(['plugins_enabled' => json_encode([['name' => 'mayaka', 'version' => '0.0.0']])]);
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')
                ->with(base_path('plugins'))
                ->once()
                ->andReturn(collect(['/mayaka']));

            $mock->shouldReceive('exists')
                ->with('/mayaka'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with('/mayaka'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(json_encode([
                    'name' => 'mayaka',
                    'version' => '0.0.0',
                ]));

            $mock->shouldReceive('exists')
                ->with('/mayaka/vendor/autoload.php')
                ->once()
                ->andReturn(false);

            $mock->shouldReceive('exists')
                ->with('/mayaka/bootstrap.php')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('getRequire')
                ->with('/mayaka/bootstrap.php')
                ->once()
                ->andReturn(function (\Illuminate\Contracts\Events\Dispatcher $events) {
                    $this->assertTrue(method_exists($events, 'listen'));
                });
        });

        $manager = $this->rebootPluginManager(app('plugins'));

        option(['plugins_enabled' => '[]']);
    }

    public function testLifecycleHooks()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')
                ->with(base_path('plugins'))
                ->once()
                ->andReturn(collect(['/mayaka']));

            $mock->shouldReceive('exists')
                ->with('/mayaka'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with('/mayaka'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(json_encode([
                    'name' => 'mayaka',
                    'version' => '0.0.0',
                    'namespace' => 'Mayaka',
                ]));

            $mock->shouldReceive('exists')
                ->with('/mayaka/callbacks.php')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('getRequire')
                ->with('/mayaka/callbacks.php')
                ->once()
                ->andReturn([
                    \App\Events\PluginWasDeleted::class => function ($plugin) {
                        $this->assertInstanceOf(Plugin::class, $plugin);
                        $this->assertEquals('mayaka', $plugin->name);
                    },
                ]);
        });

        $manager = $this->rebootPluginManager(app('plugins'));
        event(new \App\Events\PluginWasDeleted(new Plugin('/mayaka', ['name' => 'mayaka'])));
    }

    public function testRegisterAutoload()
    {
        $dir = config('plugins.directory');
        config(['plugins.directory' => storage_path('mocks')]);
        option(['plugins_enabled' => json_encode([['name' => 'fake', 'version' => '0.0.0']])]);

        $this->assertFalse(class_exists('Fake\Faker'));
        $manager = $this->rebootPluginManager(app('plugins'));
        $this->assertTrue(class_exists('Fake\Faker'));

        config(['plugins.directory' => $dir]);
        option(['plugins_enabled' => '[]']);
    }

    public function testGetUnsatisfied()
    {
        $manager = app('plugins');

        $plugin = new Plugin('', ['require' => ['blessing-skin-server' => '^0.0.0']]);
        $info = $manager->getUnsatisfied($plugin)->get('blessing-skin-server');
        $this->assertEquals(config('app.version'), $info['version']);
        $this->assertEquals('^0.0.0', $info['constraint']);

        $plugin = new Plugin('', ['require' => ['php' => '^0.0.0']]);
        $info = $manager->getUnsatisfied($plugin)->get('php');
        $this->assertEquals(PHP_VERSION, $info['version']);
        $this->assertEquals('^0.0.0', $info['constraint']);

        $plugin = new Plugin('', ['require' => ['another-plugin' => '0.0.*']]);
        $info = $manager->getUnsatisfied($plugin)->get('another-plugin');
        $this->assertNull($info['version']);
        $this->assertEquals('0.0.*', $info['constraint']);

        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('enabled');
        $property->setAccessible(true);
        $property->setValue($manager, collect(['another-plugin' => ['version' => '1.2.3']]));
        $info = $manager->getUnsatisfied($plugin)->get('another-plugin');
        $this->assertEquals('1.2.3', $info['version']);
        $this->assertEquals('0.0.*', $info['constraint']);

        $plugin = new Plugin('', ['require' => ['another-plugin' => '^1.0.0']]);
        $this->assertFalse($manager->getUnsatisfied($plugin)->has('another-plugin'));
    }

    public function testEnable()
    {
        Event::fake();

        $manager = app('plugins');
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('plugins');
        $property->setAccessible(true);
        $plugin = new Plugin('', ['name' => 'fake']);
        $property->setValue($manager, collect(['fake' => $plugin]));

        $manager->enable('fake');
        Event::assertDispatched(Events\PluginWasEnabled::class, function ($event) {
            $this->assertEquals('fake', $event->plugin->name);

            return true;
        });
        $this->assertTrue($manager->getEnabledPlugins()->has('fake'));
        $this->assertEquals(
            'fake',
            json_decode(resolve(\App\Services\Option::class)->get('plugins_enabled'), true)[0]['name']
        );
    }

    public function testDisable()
    {
        Event::fake();

        $manager = app('plugins');
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('plugins');
        $property->setAccessible(true);
        $plugin = new Plugin('', ['name' => 'fake']);
        $plugin->setEnabled(true);
        $property->setValue($manager, collect(['fake' => $plugin]));

        $manager->disable('fake');
        Event::assertDispatched(Events\PluginWasDisabled::class, function ($event) {
            $this->assertEquals('fake', $event->plugin->name);

            return true;
        });
        $this->assertFalse($manager->getEnabledPlugins()->has('fake'));
        $this->assertCount(0, json_decode(resolve(\App\Services\Option::class)->get('plugins_enabled'), true));
    }

    public function testDelete()
    {
        Event::fake();
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')->andReturn(collect([]));
            $mock->shouldReceive('deleteDirectory')->with('/fake')->once();
        });

        $manager = app('plugins');
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('plugins');
        $property->setAccessible(true);
        $plugin = new Plugin('/fake', ['name' => 'fake']);
        $plugin->setEnabled(true);
        $property->setValue($manager, collect(['fake' => $plugin]));

        $manager->delete('fake');
        Event::assertDispatched(Events\PluginWasDisabled::class, function ($event) {
            $this->assertEquals('fake', $event->plugin->name);

            return true;
        });
        Event::assertDispatched(Events\PluginWasDeleted::class, function ($event) {
            $this->assertEquals('fake', $event->plugin->name);

            return true;
        });
        $this->assertFalse($manager->getEnabledPlugins()->has('fake'));
        $this->assertCount(0, json_decode(resolve(\App\Services\Option::class)->get('plugins_enabled'), true));
        $this->assertTrue($manager->all()->isEmpty());
    }

    public function testHelpers()
    {
        $manager = app('plugins');
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('plugins');
        $property->setAccessible(true);
        $property->setValue($manager, collect(['fake' => new Plugin('', ['name' => 'fake', 'version' => '1'])]));

        $this->assertNull(plugin('nope'));
        $this->assertInstanceOf(Plugin::class, plugin('fake'));

        $this->assertEquals(
            url('plugins').'/fake/assets/relative?v=1',
            plugin_assets('fake', 'relative')
        );

        $this->expectExceptionMessage('No such plugin.');
        plugin_assets('nope', 'relative');
    }
}
