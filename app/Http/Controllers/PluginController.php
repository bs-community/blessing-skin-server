<?php

namespace App\Http\Controllers;

use View;
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

                        return redirect('admin/plugins/manage')->with('message', trans('admin.plugins.operations.enabled', ['plugin' => $plugin->title]));
                        break;

                    case 'disable':
                        $plugins->disable($id);

                        return redirect('admin/plugins/manage')->with('message', trans('admin.plugins.operations.disabled', ['plugin' => $plugin->title]));
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

        $data = [
            'installed' => $plugins->getPlugins(),
            'enabled'   => $plugins->getEnabledPlugins()
        ];

        return view('admin.plugins', $data);
    }
}
