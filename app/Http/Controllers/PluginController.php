<?php

namespace App\Http\Controllers;

use View;
use Datatables;
use App\Events;
use Illuminate\Http\Request;
use App\Services\PluginManager;

class PluginController extends Controller
{
    public function showMarket()
    {
        return "developing";
    }

    public function manage(Request $request, PluginManager $plugins)
    {
        if ($request->has('action') && $request->has('id')) {
            $id = $request->get('id');

            if ($plugins->getPlugins()->has($id)) {
                $plugin = $plugins->getPlugin($id);

                // pass the plugin title through the translator
                $plugin->title = trans($plugin->title);

                switch ($request->get('action')) {
                    case 'enable':
                        $plugins->enable($id);

                        return json(trans('admin.plugins.operations.enabled', ['plugin' => $plugin->title]), 0);
                        break;

                    case 'disable':
                        $plugins->disable($id);

                        return json(trans('admin.plugins.operations.disabled', ['plugin' => $plugin->title]), 0);
                        break;

                    case 'delete':
                        if ($request->isMethod('post')) {
                            event(new Events\PluginWasDeleted($plugin));

                            $plugins->uninstall($id);

                            return json(trans('admin.plugins.operations.deleted'), 0);
                        }
                        break;

                    case 'config':
                        if ($plugin->isEnabled() && $plugin->hasConfigView()) {
                            return View::file($plugin->getViewPath('config'));
                        } else {
                            abort(404);
                        }

                        break;

                    default:
                        # code...
                        break;
                }
            }

        }

        return view('admin.plugins');
    }

    public function getPluginData(PluginManager $plugins)
    {
        $installed = $plugins->getPlugins();

        return Datatables::of($installed)
            ->setRowId('plugin-{{ $name }}')
            ->editColumn('title', function ($plugin) {
                return trans($plugin->title);
            })
            ->editColumn('description', function ($plugin) {
                return trans($plugin->description);
            })
            ->addColumn('status', function ($plugin) {
                return trans('admin.plugins.status.'.($plugin->isEnabled() ? 'enabled' : 'disabled'));
            })
            ->addColumn('operations', function ($plugin) {
                return view('vendor.admin-operations.plugins.operations', compact('plugin'));
            })
            ->make(true);
    }
}
