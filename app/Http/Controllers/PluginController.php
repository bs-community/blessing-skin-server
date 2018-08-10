<?php

namespace App\Http\Controllers;

use View;
use Datatables;
use App\Events;
use App\Services\Plugin;
use Illuminate\Http\Request;
use App\Services\PluginManager;

class PluginController extends Controller
{
    /**
     * @codeCoverageIgnore
     */
    public function showMarket()
    {
        return redirect('/')->setTargetUrl(
            'https://github.com/printempw/blessing-skin-server/wiki/Plugins'
        );
    }

    public function showManage()
    {
        return view('admin.plugins');
    }

    public function config($name, Request $request)
    {
        $plugin = plugin($name);

        if ($plugin && $plugin->isEnabled() && $plugin->hasConfigView()) {
            return $plugin->getConfigView();
        } else {
            return abort(404, trans('admin.plugins.operations.no-config-notice'));
        }
    }

    public function manage(Request $request, PluginManager $plugins)
    {
        $plugin = plugin($name = $request->get('name'));

        if ($plugin) {
            // Pass the plugin title through the translator.
            $plugin->title = trans($plugin->title);

            switch ($request->get('action')) {
                case 'enable':
                    if (! $plugins->isRequirementsSatisfied($plugin)) {
                        $reason = [];

                        foreach ($plugins->getUnsatisfiedRequirements($plugin) as $name => $detail) {
                            $constraint = $detail['constraint'];

                            if (! $detail['version']) {
                                $reason[] = trans('admin.plugins.operations.unsatisfied.disabled', compact('name'));
                            } else {
                                $reason[] = trans('admin.plugins.operations.unsatisfied.version', compact('name', 'constraint'));
                            }
                        }

                        return json([
                            'errno' => 1,
                            'msg' => trans('admin.plugins.operations.unsatisfied.notice'),
                            'reason' => $reason
                        ]);
                    }

                    $plugins->enable($name);

                    return json(trans('admin.plugins.operations.enabled', ['plugin' => $plugin->title]), 0);

                case 'requirements':
                    return json($this->getPluginDependencies($plugin));

                case 'disable':
                    $plugins->disable($name);

                    return json(trans('admin.plugins.operations.disabled', ['plugin' => $plugin->title]), 0);

                case 'delete':
                    $plugins->uninstall($name);

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
        $installed = $plugins->getPlugins();

        return Datatables::of($installed)
            ->setRowId('plugin-{{ $name }}')
            ->editColumn('title', '{{ trans($title ?: "EMPTY") }}')
            ->editColumn('description', '{{ trans($description ?: "EMPTY") }}')
            ->addColumn('enabled', function ($plugin) { return $plugin->isEnabled(); })
            ->addColumn('config', function ($plugin) { return $plugin->hasConfigView(); })
            ->addColumn('dependencies', function ($plugin) {
                return $this->getPluginDependencies($plugin);
            })
            ->make(true);
    }

    protected function getPluginDependencies(Plugin $plugin)
    {
        $plugins = app('plugins');

        return [
            'isRequirementsSatisfied' => $plugins->isRequirementsSatisfied($plugin),
            'requirements' => $plugin->getRequirements(),
            'unsatisfiedRequirements' => $plugins->getUnsatisfiedRequirements($plugin)
        ];
    }
}
