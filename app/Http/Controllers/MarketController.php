<?php

namespace App\Http\Controllers;

use Exception;
use App\Services\Plugin;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Services\PluginManager;
use Composer\Semver\Comparator;
use App\Services\PackageManager;

class MarketController extends Controller
{
    /**
     * Guzzle HTTP client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;

    /**
     * Cache for plugins registry.
     *
     * @var array
     */
    protected $registryCache;

    public function __construct(\GuzzleHttp\Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function marketData(PluginManager $manager)
    {
        $plugins = collect($this->getAllAvailablePlugins())->map(function ($item) use ($manager) {
            $plugin = $manager->get($item['name']);

            if ($plugin) {
                $item['enabled'] = $plugin->isEnabled();
                $item['installed'] = $plugin->version;
                $item['update_available'] = Comparator::greaterThan($item['version'], $item['installed']);
            } else {
                $item['installed'] = false;
            }

            $requirements = Arr::get($item, 'require', []);
            unset($item['require']);

            $item['dependencies'] = [
                'all' => $requirements,
                'unsatisfied' => $manager->getUnsatisfied(new Plugin('', $item)),
            ];

            return $item;
        });

        return $plugins;
    }

    public function checkUpdates(PluginManager $manager)
    {
        $pluginsHaveUpdate = collect($this->getAllAvailablePlugins())
            ->filter(function ($item) use ($manager) {
                $plugin = $manager->get($item['name']);

                return $plugin && Comparator::greaterThan($item['version'], $plugin->version);
            });

        return json([
            'available' => $pluginsHaveUpdate->isNotEmpty(),
            'plugins' => $pluginsHaveUpdate->values()->all(),
        ]);
    }

    public function download(Request $request, PluginManager $manager, PackageManager $package)
    {
        $name = $request->get('name');
        $metadata = $this->getPluginMetadata($name);

        if (! $metadata) {
            return json(trans('admin.plugins.market.non-existent', ['plugin' => $name]), 1);
        }

        $fakePlugin = new Plugin('', $metadata);
        $unsatisfied = $manager->getUnsatisfied($fakePlugin);
        $conflicts = $manager->getConflicts($fakePlugin);
        if ($unsatisfied->isNotEmpty() || $conflicts->isNotEmpty()) {
            $reason =  $manager->formatUnresolved($unsatisfied, $conflicts);

            return json(trans('admin.plugins.market.unresolved'), 1, compact('reason'));
        }

        $url = $metadata['dist']['url'];
        $filename = Arr::last(explode('/', $url));
        $pluginsDir = $manager->getPluginsDirs()->first();
        $path = storage_path("packages/$name".'_'.$metadata['version'].'.zip');

        try {
            $package->download($url, $path, $metadata['dist']['shasum'])->extract($pluginsDir);
        } catch (Exception $e) {
            return json($e->getMessage(), 1);
        }

        return json(trans('admin.plugins.market.install-success'), 0);
    }

    protected function getPluginMetadata($name)
    {
        return collect($this->getAllAvailablePlugins())->firstWhere('name', $name);
    }

    protected function getAllAvailablePlugins()
    {
        $registryVersion = 1;
        if (app()->runningUnitTests() || ! $this->registryCache) {
            $registries = collect(explode(',', config('plugins.registry')));
            $this->registryCache = $registries->map(function ($registry) use ($registryVersion) {
                try {
                    $pluginsJson = $this->guzzle->request(
                        'GET',
                        trim($registry),
                        ['verify' => \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()]
                    )->getBody();
                } catch (Exception $e) {
                    throw new Exception(trans('admin.plugins.market.connection-error', [
                        'error' => htmlentities($e->getMessage()),
                    ]));
                }

                $registryData = json_decode($pluginsJson, true);
                $received = Arr::get($registryData, 'version');
                abort_if(
                    is_int($received) && $received != $registryVersion,
                    500,
                    "Only version $registryVersion of market registry is accepted."
                );

                return Arr::get($registryData, 'packages', []);
            })->flatten(1);
        }

        return $this->registryCache;
    }
}
