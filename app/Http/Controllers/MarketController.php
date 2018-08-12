<?php

namespace App\Http\Controllers;

use Exception;
use Datatables;
use ZipArchive;
use Illuminate\Http\Request;
use Composer\Semver\Comparator;
use App\Services\PluginManager;

class MarketController extends Controller
{
    protected $registryCache;

    public function showMarket()
    {
        return view('admin.market');
    }

    public function getMarketData()
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

            $requirements = array_get($item, 'require', []);
            unset($item['require']);

            $item['dependencies'] = [
                'isRequirementsSatisfied' => $manager->isRequirementsSatisfied($requirements),
                'requirements' => $requirements,
                'unsatisfiedRequirements' => $manager->getUnsatisfiedRequirements($requirements)
            ];

            return $item;
        });

        return Datatables::of($plugins)->setRowId('plugin-{{ $name }}')->make(true);
    }

    public function checkUpdates()
    {
        $pluginsHaveUpdate = collect($this->getAllAvailablePlugins())->filter(function ($item) {
            $plugin = plugin($item['name']);
            return $plugin && Comparator::greaterThan($item['version'], $plugin->version);
        });

        return json([
            'available' => !$pluginsHaveUpdate->isEmpty(),
            'plugins' => array_values($pluginsHaveUpdate->all())
        ]);
    }

    public function download(Request $request, PluginManager $manager)
    {
        $name = $request->get('name');
        $metadata = $this->getPluginMetadata($name);

        if (! $metadata) {
            return json(trans('admin.plugins.market.non-existent', ['plugin' => $name]), 1);
        }

        // Gather plugin distribution URL
        $url = $metadata['dist']['url'];
        $filename = array_last(explode('/', $url));
        $plugins_dir = $manager->getPluginsDir();
        $tmp_path = $plugins_dir.DIRECTORY_SEPARATOR.$filename;
        $client = new \GuzzleHttp\Client();

        // Download
        try {
            $client->request('GET', $url, [
                'headers' => ['User-Agent' => config('secure.user_agent')],
                'verify' => config('secure.certificates'),
                'sink' => $tmp_path
            ]);
        } catch (Exception $e) {
            report($e);
            return json(trans('admin.plugins.market.download-failed', ['error' => $e->getMessage()]), 2);
        }

        // Check file's sha1 hash
        if (sha1_file($tmp_path) !== $metadata['dist']['shasum']) {
            @unlink($tmp_path);
            return json(trans('admin.plugins.market.shasum-failed'), 3);
        }

        // Unzip
        $zip = new ZipArchive();
        $res = $zip->open($tmp_path);

        if ($res === true) {
            if ($zip->extractTo($plugins_dir) === false) {
                return json(trans('admin.plugins.market.unzip-failed', ['error' => 'Unable to extract the file.']), 4);
            }
        } else {
            return json(trans('admin.plugins.market.unzip-failed', ['error' => $res]), 4);
        }
        $zip->close();
        @unlink($tmp_path);

        return json(trans('admin.plugins.market.install-success'), 0);
    }

    protected function getPluginMetadata($name)
    {
        return collect($this->getAllAvailablePlugins())->where('name', $name)->first();
    }

    protected function getAllAvailablePlugins()
    {
        if (! $this->registryCache) {
            $client = new \GuzzleHttp\Client();

            try {
                $pluginsJson = $client->request('GET', config('plugins.registry'), [
                    'headers' => ['User-Agent' => config('secure.user_agent')],
                    'verify' => config('secure.certificates')
                ])->getBody();
            } catch (Exception $e) {
                throw new Exception(trans('admin.plugins.market.connection-error', [
                    'error' => htmlentities($e->getMessage())
                ]));
            }

            $this->registryCache = json_decode($pluginsJson, true);
        }

        return array_get($this->registryCache, 'packages', []);
    }
}
