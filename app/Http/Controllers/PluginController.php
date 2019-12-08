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

        $title = $plugin->title;
        $path = $plugin->getPath().'/'.$readmePath;
        $content = resolve('parsedown')->text(file_get_contents($path));

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
                        $unsatisfied = $result['unsatisfied']->map(function ($detail, $name) use ($plugins) {
                            $constraint = $detail['constraint'];
                            if (! $detail['version']) {
                                $plugin = $plugins->get($name);
                                $name = $plugin ? trans($plugin->title) : $name;

                                return trans('admin.plugins.operations.unsatisfied.disabled', compact('name'));
                            } else {
                                $title = trans($plugins->get($name)->title);

                                return trans('admin.plugins.operations.unsatisfied.version', compact('title', 'constraint'));
                            }
                        })->values()->all();

                        $conflicts = $result['conflicts']->map(function ($detail, $name) use ($plugins) {
                            $title = trans($plugins->get($name)->title);

                            return trans('admin.plugins.operations.unsatisfied.conflict', compact('title'));
                        })->values()->all();

                        $reason = array_merge($unsatisfied, $conflicts);

                        return json(trans('admin.plugins.operations.unsatisfied.notice'), 1, compact('reason'));
                    }

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
                    'readme' => (bool) $plugin->getReadme(),
                    'config' => $plugin->hasConfig(),
                    'dependencies' => [
                        'all' => $plugin->require,
                        'unsatisfied' => $plugins->getUnsatisfied($plugin),
                    ],
                ];
            })
            ->values();
    }
}
