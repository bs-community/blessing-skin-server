<?php

namespace App\Http\Controllers;

use Exception;
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

    public function marketData()
    {
        $plugins = collect($this->getAllAvailablePlugins())->map(function ($item) {
            $plugin = plugin($item['name']);
            $manager = app('plugins');

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
                'isRequirementsSatisfied' => $manager->isRequirementsSatisfied($requirements),
                'requirements' => $requirements,
                'unsatisfiedRequirements' => $manager->getUnsatisfiedRequirements($requirements),
            ];

            return $item;
        });

        return $plugins;
    }

    public function checkUpdates()
    {
        $pluginsHaveUpdate = collect($this->getAllAvailablePlugins())->filter(function ($item) {
            $plugin = plugin($item['name']);

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

        $url = $metadata['dist']['url'];
        $filename = Arr::last(explode('/', $url));
        $pluginsDir = $manager->getPluginsDir();
        $path = storage_path("packages/$filename");

        try {
            $package->download($url, $path, $metadata['dist']['shasum'])->extract($pluginsDir);
        } catch (Exception $e) {
            return json($e->getMessage(), 1);
        }

        return json(trans('admin.plugins.market.install-success'), 0);
    }

    protected function getPluginMetadata($name)
    {
        return collect($this->getAllAvailablePlugins())->where('name', $name)->first();
    }

    protected function getAllAvailablePlugins()
    {
        $registryVersion = 1;
        if (app()->environment('testing') || ! $this->registryCache) {
            try {
                $pluginsJson = $this->guzzle->request(
                    'GET', config('plugins.registry')
                )->getBody();
            } catch (Exception $e) {
                throw new Exception(trans('admin.plugins.market.connection-error', [
                    'error' => htmlentities($e->getMessage()),
                ]));
            }

            $this->registryCache = json_decode($pluginsJson, true);
            $received = Arr::get($this->registryCache, 'version');
            if (is_int($received) && $received != $registryVersion) {
                throw new Exception("Only version $registryVersion of market registry is accepted.");
            }
        }

        return Arr::get($this->registryCache, 'packages', []);
    }
}
