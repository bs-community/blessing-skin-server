<?php

namespace Tests;

use App\Events;
use App\Services\Option;
use App\Services\Plugin;
use App\Services\PluginManager;
use Event;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;

class PluginManagerTest extends TestCase
{
    public function testPreventBootingAgain()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')->times(0);
        });
        resolve(PluginManager::class)->boot();
    }

    public function testNotLoadDisabled()
    {
        $dir = config('plugins.directory');
        config(['plugins.directory' => base_path('tests/__mocks__')]);

        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->boot();
        $this->assertFalse(class_exists('Fake\Faker'));

        config(['plugins.directory' => $dir]);
    }

    public function testNotLoadUnsatisfied()
    {
        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')->with('plugins_enabled', '[]')->andReturn('[]');
        });
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

        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->boot();
    }

    public function testReportDuplicatedPlugins()
    {
        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')->with('plugins_enabled', '[]')->andReturn('[]');
        });
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
        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->boot();
    }

    public function testDetectVersionChanged()
    {
        Event::fake();
        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('plugins_enabled', '[]')
                ->andReturn(json_encode([['name' => 'mayaka', 'version' => '0.0.0']]));
            $mock->shouldReceive('set')
                ->with(
                    'plugins_enabled',
                    json_encode([['name' => 'mayaka', 'version' => '0.1.0']])
                )
                ->once();
        });
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

        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->boot();
        Event::assertDispatched('plugin.versionChanged', function ($eventName, $payload) {
            $this->assertEquals('0.1.0', $payload[0]->version);

            return true;
        });
    }

    public function testLoadComposer()
    {
        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('plugins_enabled', '[]')
                ->andReturn(json_encode([['name' => 'mayaka', 'version' => '0.0.0']]));
        });
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

        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->boot();
    }

    public function testLoadViews()
    {
        $dir = config('plugins.directory');
        config(['plugins.directory' => base_path('tests/__mocks__')]);
        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('plugins_enabled', '[]')
                ->andReturn(json_encode([
                    ['name' => 'fake-with-views', 'version' => '0.0.0'],
                ]));
        });

        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->boot();
        $this->assertTrue(view()->exists('FakeWithViews::example'));

        config(['plugins.directory' => $dir]);
    }

    public function testLoadTranslations()
    {
        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('plugins_enabled', '[]')
                ->andReturn(json_encode([['name' => 'mayaka', 'version' => '0.0.0']]));
        });
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')
                ->with(base_path('plugins'))
                ->once()
                ->andReturn(collect(['/mayaka', '/chitanda']));

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

            $mock->shouldReceive('exists')
                ->with('/chitanda'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with('/chitanda'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(json_encode([
                    'name' => 'chitanda',
                    'version' => '0.0.0',
                    'namespace' => 'Chitanda',
                ]));
        });
        $this->instance('translation.loader', \Mockery::mock(\App\Services\Translations\Loader::class, function ($mock) {
            $mock->shouldReceive('addNamespace')
                ->withArgs(['Mayaka', '/mayaka/lang'])
                ->once();

            $mock->shouldReceive('addNamespace')
                ->withArgs(['Chitanda', '/chitanda/lang'])
                ->once();
        }));

        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->boot();
    }

    public function testLoadBootstrapper()
    {
        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('plugins_enabled', '[]')
                ->andReturn(json_encode([['name' => 'mayaka', 'version' => '0.0.0']]));
        });
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
                ->andReturn(function (\Illuminate\Contracts\Events\Dispatcher $events, Plugin $plugin) {
                    $this->assertTrue(method_exists($events, 'listen'));
                    $this->assertEquals('mayaka', $plugin->name);
                });
        });

        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->boot();
    }

    public function testHandleBootstrapperExceptions()
    {
        Event::fake();
        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('plugins_enabled', '[]')
                ->andReturn(json_encode([['name' => 'mayaka', 'version' => '0.0.0']]));
        });
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')
                ->with(base_path('plugins'))
                ->andReturn(collect(['/mayaka']));

            $mock->shouldReceive('exists')
                ->with('/mayaka'.DIRECTORY_SEPARATOR.'package.json')
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with('/mayaka'.DIRECTORY_SEPARATOR.'package.json')
                ->andReturn(json_encode([
                    'name' => 'mayaka',
                    'version' => '0.0.0',
                ]));

            $mock->shouldReceive('exists')
                ->with('/mayaka/vendor/autoload.php')
                ->andReturn(false);

            $mock->shouldReceive('exists')
                ->with('/mayaka/bootstrap.php')
                ->andReturn(true);

            $mock->shouldReceive('getRequire')
                ->with('/mayaka/bootstrap.php')
                ->andReturn(function () {
                    throw new \Exception();
                }, fn () => abort(500));
        });

        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->boot();
        Event::assertDispatched(Events\PluginBootFailed::class, function ($event) {
            $this->assertEquals('mayaka', $event->plugin->name);

            return true;
        });

        app()->forgetInstance(PluginManager::class);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        resolve(PluginManager::class)->boot();
    }

    public function testLifecycleHooks()
    {
        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')->with('plugins_enabled', '[]')->andReturn('[]');
        });
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

        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->boot();
        event(new \App\Events\PluginWasDeleted(new Plugin('/mayaka', ['name' => 'mayaka'])));
    }

    public function testRegisterAutoload()
    {
        $dir = config('plugins.directory');
        config(['plugins.directory' => base_path('tests/__mocks__')]);
        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('plugins_enabled', '[]')
                ->andReturn(json_encode([['name' => 'fake', 'version' => '0.0.0']]));
        });

        $this->assertFalse(class_exists('Fake\Faker'));
        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->boot();
        $this->assertTrue(class_exists('Fake\FakeServiceProvider'));

        config(['plugins.directory' => $dir]);
    }

    public function testRegisterServiceProviders()
    {
        Event::fake();

        $dir = config('plugins.directory');
        config(['plugins.directory' => base_path('tests/__mocks__')]);
        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('plugins_enabled', '[]')
                ->andReturn(json_encode([['name' => 'fake', 'version' => '0.0.0']]));
        });

        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->boot();
        Event::assertDispatched('provider.loaded');

        config(['plugins.directory' => $dir]);
    }

    public function testGetUnsatisfied()
    {
        $manager = resolve(PluginManager::class);

        $plugin = new Plugin('', ['require' => ['blessing-skin-server' => '^0.0.0']]);
        $info = $manager->getUnsatisfied($plugin)->get('blessing-skin-server');
        $this->assertEquals(config('app.version'), $info['version']);
        $this->assertEquals('^0.0.0', $info['constraint']);

        preg_match('/(\d+\.\d+\.\d+)/', PHP_VERSION, $matches);
        $version = $matches[1];
        $plugin = new Plugin('', ['require' => ['php' => '^0.0.0']]);
        $info = $manager->getUnsatisfied($plugin)->get('php');
        $this->assertEquals($version, $info['version']);
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

    public function testGetConflicts()
    {
        $manager = resolve(PluginManager::class);

        $plugin = new Plugin('/', ['enchants' => ['conflicts' => ['a' => '*', 'b' => '^1.2.0']]]);
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('enabled');
        $property->setAccessible(true);
        $property->setValue($manager, collect(['b' => ['version' => '1.2.3']]));

        $conflicts = $manager->getConflicts($plugin);
        $this->assertNull($conflicts->get('a'));
        $info = $conflicts->get('b');
        $this->assertEquals('1.2.3', $info['version']);
        $this->assertEquals('^1.2.0', $info['constraint']);

        $plugin = new Plugin('/', ['enchants' => ['conflicts' => ['b' => '^0.0.0']]]);
        $this->assertNull($manager->getConflicts($plugin)->get('b'));
    }

    public function testFormatUnresolved()
    {
        app()->forgetInstance(PluginManager::class);
        $manager = resolve(PluginManager::class);
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('plugins');
        $property->setAccessible(true);
        $property->setValue($manager, collect([
            'dep' => new Plugin('', ['title' => 'dep', 'version' => '0.0.0']),
            'conf' => new Plugin('', ['title' => 'conf', 'version' => '1.2.3']),
        ]));

        $unsatisfied = collect([
            'blessing-skin-server' => ['version' => '4.0.0', 'constraint' => '^5.0.0'],
            'php' => ['version' => '7.2.0', 'constraint' => '^7.3.0'],
            'dep' => ['version' => '0.0.0', 'constraint' => '^6.6.6'],
            'whatever' => ['version' => null, 'constraint' => '^1.2.3'],
        ]);
        $conflicts = collect([
            'conf' => ['version' => '1.2.3', 'constraint' => '^1.0.0'],
        ]);

        $received = $manager->formatUnresolved($unsatisfied, $conflicts);
        $expected = [
            trans('admin.plugins.operations.unsatisfied.version', [
                'title' => 'Blessing Skin Server',
                'constraint' => '^5.0.0',
            ]),
            trans('admin.plugins.operations.unsatisfied.version', [
                'title' => 'PHP',
                'constraint' => '^7.3.0',
            ]),
            trans('admin.plugins.operations.unsatisfied.version', [
                'title' => 'dep',
                'constraint' => '^6.6.6',
            ]),
            trans('admin.plugins.operations.unsatisfied.disabled', ['name' => 'whatever']),
            trans('admin.plugins.operations.unsatisfied.conflict', ['title' => 'conf']),
        ];
        $this->assertEquals($expected, $received);
    }

    public function testEnable()
    {
        Event::fake();

        app()->forgetInstance(PluginManager::class);
        $manager = resolve(PluginManager::class);
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('plugins');
        $property->setAccessible(true);
        $property->setValue($manager, collect([
            'fake' => new Plugin('', ['name' => 'fake']),
            'dep' => new Plugin('', ['name' => 'dep', 'require' => ['a' => '*']]),
        ]));

        $this->assertFalse($manager->enable('nope'));

        $this->assertTrue($manager->enable('fake'));

        // re-enable should be allowed
        $this->assertTrue($manager->enable('fake'));

        Event::assertDispatched(Events\PluginWasEnabled::class, function ($event) {
            $this->assertEquals('fake', $event->plugin->name);

            return true;
        });
        $this->assertTrue($manager->getEnabledPlugins()->has('fake'));
        $this->assertEquals(
            'fake',
            json_decode(resolve(\App\Services\Option::class)->get('plugins_enabled'), true)[0]['name']
        );

        $this->assertTrue($manager->enable('dep')['unsatisfied']->isNotEmpty());
    }

    public function testDisable()
    {
        Event::fake();

        app()->forgetInstance(PluginManager::class);
        $manager = resolve(PluginManager::class);
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
        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('plugins_enabled', '[]')
                ->andReturn(json_encode([['name' => 'fake', 'version' => '0.0.0']]));
            $mock->shouldReceive('set')
                ->with('plugins_enabled', '[]');
        });
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')->andReturn(collect([]));
            $mock->shouldReceive('deleteDirectory')->with('/fake')->once();
        });

        app()->forgetInstance(PluginManager::class);
        $manager = resolve(PluginManager::class);
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

    public function testDefaultPluginsDirectory()
    {
        $old = config('plugins.directory');
        config(['plugins.directory' => null]);

        $directories = app('plugins')->getPluginsDirs();
        $this->assertEquals(1, $directories->count());
        $this->assertEquals(base_path('plugins'), $directories->first());

        config(['plugins.directory' => $old]);
    }

    public function testReadMultipleDirectories()
    {
        $old = config('plugins.directory');
        config(['plugins.directory' => '/kumiko,/reina']);

        $this->mock(Option::class, function ($mock) {
            $mock->shouldReceive('get')->with('plugins_enabled', '[]')->andReturn('[]');
        });
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('directories')
                ->with('/kumiko')
                ->once()
                ->andReturn(collect(['/a', '/b']));
            $mock->shouldReceive('directories')
                ->with('/reina')
                ->once()
                ->andReturn(collect(['/b', '/c']));

            $mock->shouldReceive('exists')
                ->with('/a'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(false);
            $mock->shouldReceive('exists')
                ->with('/b'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(false);
            $mock->shouldReceive('exists')
                ->with('/c'.DIRECTORY_SEPARATOR.'package.json')
                ->once()
                ->andReturn(false);
        });
        app()->forgetInstance(PluginManager::class);
        resolve(PluginManager::class)->all();

        config(['plugins.directory' => $old]);
    }
}
