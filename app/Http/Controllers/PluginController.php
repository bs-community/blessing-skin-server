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
                        $msg = '';

                        foreach ($plugins->getUnsatisfiedRequirements($plugin) as $name => $detail) {
                            if (! $detail['version']) {
                                $msg .= '<li>'.trans('admin.plugins.operations.unsatisfied.disabled', [
                                    'name' => "<code>$name</code>"
                                ]).'</li>';
                            } else {
                                $msg .= '<li>'.trans('admin.plugins.operations.unsatisfied.version', [
                                    'name' => "<code>$name</code>",
                                    'constraint' => "<code>{$detail['constraint']}</code>"
                                ]).'</li>';
                            }
                        }

                        return json('<p>'.trans('admin.plugins.operations.unsatisfied.notice')."</p><ul>$msg</ul>", 1);
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
            ->editColumn('title', function ($plugin) {
                return trans($plugin->title ?: 'EMPTY');
            })
            ->editColumn('description', function ($plugin) {
                return trans($plugin->description ?: 'EMPTY');
            })
            ->editColumn('author', function ($plugin) {
                return ['author' => trans($plugin->author ?: 'EMPTY'), 'url' => $plugin->url];
            })
            ->addColumn('dependencies', function ($plugin) {
                return $this->getPluginDependencies($plugin);
            })
            ->addColumn('status', function ($plugin) {
                return trans('admin.plugins.status.'.($plugin->isEnabled() ? 'enabled' : 'disabled'));
            })
            ->addColumn('operations', function ($plugin) {
                return ['enabled' => $plugin->isEnabled(), 'hasConfigView' => $plugin->hasConfigView()];
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
