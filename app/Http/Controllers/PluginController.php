<?php

namespace App\Http\Controllers;

use App\Services\Plugin;
use App\Services\PluginManager;
use App\Services\Unzip;
use Composer\CaBundle\CaBundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class PluginController extends Controller
{
    public function config(PluginManager $plugins, $name)
    {
        $plugin = $plugins->get($name);
        if ($plugin && $plugin->isEnabled()) {
            if ($plugin->hasConfigClass()) {
                return app()->call($plugin->getConfigClass().'@render');
            } elseif ($plugin->hasConfigView()) {
                return $plugin->getConfigView();
            } else {
                return abort(404, trans('admin.plugins.operations.no-config-notice'));
            }
        } else {
            return abort(404, trans('admin.plugins.operations.no-config-notice'));
        }
    }

    public function readme(PluginManager $plugins, $name)
    {
        $plugin = $plugins->get($name);
        if (empty($plugin)) {
            return abort(404, trans('admin.plugins.operations.no-readme-notice'));
        }

        $readmePath = $plugin->getReadme();
        if (empty($readmePath)) {
            return abort(404, trans('admin.plugins.operations.no-readme-notice'));
        }

        $title = trans($plugin->title);
        $path = $plugin->getPath().'/'.$readmePath;
        $converter = new GithubFlavoredMarkdownConverter();
        $content = $converter->convertToHtml(file_get_contents($path));

        return view('admin.plugin.readme', compact('content', 'title'));
    }

    public function manage(Request $request, PluginManager $plugins)
    {
        $name = $request->input('name');
        $plugin = $plugins->get($name);

        if ($plugin) {
            // Pass the plugin title through the translator.
            $plugin->title = trans($plugin->title);

            switch ($request->get('action')) {
                case 'enable':
                    $result = $plugins->enable($name);

                    if ($result === true) {
                        return json(trans('admin.plugins.operations.enabled', ['plugin' => $plugin->title]), 0);
                    } else {
                        $reason = $plugins->formatUnresolved($result['unsatisfied'], $result['conflicts']);

                        return json(trans('admin.plugins.operations.unsatisfied.notice'), 1, compact('reason'));
                    }

                    // no break
                case 'disable':
                    $plugins->disable($name);

                    return json(trans('admin.plugins.operations.disabled', ['plugin' => $plugin->title]), 0);

                case 'delete':
                    $plugins->delete($name);

                    return json(trans('admin.plugins.operations.deleted'), 0);

                default:
                    return json(trans('admin.invalid-action'), 1);
            }
        } else {
            return json(trans('admin.plugins.operations.not-found'), 1);
        }
    }

    public function getPluginData(PluginManager $plugins)
    {
        return $plugins->all()
            ->map(function (Plugin $plugin) {
                return [
                    'name' => $plugin->name,
                    'title' => trans($plugin->title),
                    'description' => trans($plugin->description ?? ''),
                    'version' => $plugin->version,
                    'enabled' => $plugin->isEnabled(),
                    'readme' => (bool) $plugin->getReadme(),
                    'config' => $plugin->hasConfig(),
                    'icon' => array_merge(
                        ['fa' => 'plug', 'faType' => 'fas', 'bg' => 'navy'],
                        $plugin->getManifestAttr('enchants.icon', [])
                    ),
                ];
            })
            ->values();
    }

    public function upload(Request $request, PluginManager $manager, Unzip $unzip)
    {
        $request->validate(['file' => 'required|file|mimetypes:application/zip']);

        $path = $request->file('file')->getPathname();
        $unzip->extract($path, $manager->getPluginsDirs()->first());

        return json(trans('admin.plugins.market.install-success'), 0);
    }

    public function wget(Request $request, PluginManager $manager, Unzip $unzip)
    {
        $data = $request->validate(['url' => 'required|url']);

        $path = tempnam(sys_get_temp_dir(), 'wget-plugin');
        $response = Http::withOptions([
            'sink' => $path,
            'verify' => CaBundle::getSystemCaRootBundlePath(),
        ])->get($data['url']);

        if ($response->ok()) {
            $unzip->extract($path, $manager->getPluginsDirs()->first());

            return json(trans('admin.plugins.market.install-success'), 0);
        } else {
            return json(trans('admin.download.errors.download', ['error' => $response->status()]), 1);
        }
    }
}
