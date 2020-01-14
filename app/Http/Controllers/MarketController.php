<?php

namespace App\Http\Controllers;

use App\Services\Plugin;
use App\Services\PluginManager;
use App\Services\Unzip;
use Composer\CaBundle\CaBundle;
use Composer\Semver\Comparator;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MarketController extends Controller
{
    public function marketData(PluginManager $manager, Client $client)
    {
        $plugins = $this->fetch($client)->map(function ($item) use ($manager) {
            $plugin = $manager->get($item['name']);

            if ($plugin) {
                $item['enabled'] = $plugin->isEnabled();
                $item['installed'] = $plugin->version;
                $item['can_update'] = Comparator::greaterThan($item['version'], $item['installed']);
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

    public function download(
        Request $request,
        PluginManager $manager,
        Client $client,
        Unzip $unzip
    ) {
        $name = $request->input('name');
        $plugins = $this->fetch($client);
        $metadata = $plugins->firstWhere('name', $name);

        if (!$metadata) {
            return json(trans('admin.plugins.market.non-existent', ['plugin' => $name]), 1);
        }

        $fakePlugin = new Plugin('', $metadata);
        $unsatisfied = $manager->getUnsatisfied($fakePlugin);
        $conflicts = $manager->getConflicts($fakePlugin);
        if ($unsatisfied->isNotEmpty() || $conflicts->isNotEmpty()) {
            $reason = $manager->formatUnresolved($unsatisfied, $conflicts);

            return json(trans('admin.plugins.market.unresolved'), 1, compact('reason'));
        }

        $path = tempnam(sys_get_temp_dir(), $name);
        try {
            $client->get($metadata['dist']['url'], [
                'sink' => $path,
                'verify' => CaBundle::getSystemCaRootBundlePath(),
            ]);
            $unzip->extract($path, $manager->getPluginsDirs()->first());
        } catch (Exception $e) {
            report($e);

            return json(trans('admin.download.errors.download', ['error' => $e->getMessage()]), 1);
        }

        return json(trans('admin.plugins.market.install-success'), 0);
    }

    protected function fetch(Client $client): Collection
    {
        $plugins = collect(explode(',', config('plugins.registry')))
            ->map(function ($registry) use ($client) {
                try {
                    $body = $client->get(trim($registry), [
                        'verify' => CaBundle::getSystemCaRootBundlePath(),
                    ])->getBody();
                } catch (Exception $e) {
                    throw new Exception(trans('admin.plugins.market.connection-error', ['error' => $e->getMessage()]));
                }

                return Arr::get(json_decode($body, true), 'packages', []);
            })
            ->flatten(1);

        return $plugins;
    }
}
