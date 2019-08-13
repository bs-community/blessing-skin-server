<?php

namespace App\Http\Controllers;

use App\Services\Plugin;
use Illuminate\Http\Request;
use App\Services\PluginManager;

class PluginController extends Controller
{
    public function config(PluginManager $plugins, $name)
    {
        $plugin = $plugins->get($name);
        if ($plugin && $plugin->isEnabled() && $plugin->hasConfigView()) {
            return $plugin->getConfigView();
        } else {
            return abort(404, trans('admin.plugins.operations.no-config-notice'));
        }
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
                    $requirements = $plugins->getUnsatisfied($plugin);
                    if ($requirements->isNotEmpty()) {
                        $reason = $requirements->map(function ($detail, $name) {
                            $constraint = $detail['constraint'];
                            if (! $detail['version']) {
                                return trans('admin.plugins.operations.unsatisfied.disabled', compact('name'));
                            } else {
                                return trans('admin.plugins.operations.unsatisfied.version', compact('name', 'constraint'));
                            }
                        })->values()->all();

                        return json(trans('admin.plugins.operations.unsatisfied.notice'), 1, compact('reason'));
                    }

                    $plugins->enable($name);

                    return json(trans('admin.plugins.operations.enabled', ['plugin' => $plugin->title]), 0);

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
            ->map(function ($plugin) use ($plugins) {
                return [
                    'name' => $plugin->name,
                    'title' => trans($plugin->title ?: 'EMPTY'),
                    'author' => $plugin->author,
                    'description' => trans($plugin->description ?: 'EMPTY'),
                    'version' => $plugin->version,
                    'url' => $plugin->url,
                    'enabled' => $plugin->isEnabled(),
                    'config' => $plugin->hasConfigView(),
                    'dependencies' => [
                        'all' => $plugin->require,
                        'unsatisfied' => $plugins->getUnsatisfied($plugin),
                    ],
                ];
            })
            ->values();
    }
}
