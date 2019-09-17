<?php

namespace App\Http\View\Composers;

use App\Events;
use Illuminate\View\View;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Services\PluginManager;

class SideMenuComposer
{
    /** @var Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function compose(View $view)
    {
        $type = $view->gatherData()['type'];

        $menu = config('menu');
        switch ($type) {
            case 'user':
                event(new Events\ConfigureUserMenu($menu));
                break;
            case 'explore':
                event(new Events\ConfigureExploreMenu($menu));
                break;
            case 'admin':
                event(new Events\ConfigureAdminMenu($menu));
                $menu['admin'] = $this->collectPluginConfigs($menu['admin']);
                break;
        }

        $view->with('items', array_map(function ($item) {
            return $this->transform($item);
        }, $menu[$type]));
    }

    public function transform(array $item): array
    {
        $isActive = $this->request->is(Arr::get($item, 'link'));
        foreach (Arr::get($item, 'children', []) as $k => $v) {
            if ($this->request->is(Arr::get($v, 'link'))) {
                $isActive = true;
                break;
            }
        }

        $classes = [];
        if ($isActive) {
            $classes[] = 'active menu-open';
        }

        if (Arr::has($item, 'children')) {
            $classes[] = 'treeview';
            $item['children'] = array_map(function ($item) {
                return $this->transform($item);
            }, $item['children']);
        }

        $item['classes'] = $classes;

        return $item;
    }

    public function collectPluginConfigs(array &$menu)
    {
        $menu = array_map(function ($item) {
            if (Arr::get($item, 'id') === 'plugin-configs') {
                $pluginConfigs = resolve(PluginManager::class)
                    ->getEnabledPlugins()
                    ->filter(function ($plugin) {
                        return $plugin->hasConfigView();
                    })
                    ->map(function ($plugin) {
                        return [
                            'title' => trans($plugin->title),
                            'link'  => 'admin/plugins/config/'.$plugin->name,
                            'icon'  => 'fa-circle',
                        ];
                    });

                // Don't display this menu item when no plugin config is available
                if ($pluginConfigs->isNotEmpty()) {
                    $item['children'] = array_merge($item['children'], $pluginConfigs->values()->all());

                    return $item;
                }
            } else {
                return $item;
            }
        }, $menu);

        return array_filter($menu); // Remove empty items
    }
}
