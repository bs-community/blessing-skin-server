<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use App\Services\PluginManager;
use Blessing\Filter;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index(Filter $filter)
    {
        $grid = [
            'layout' => [
                ['md-6', 'md-6'],
            ],
            'widgets' => [
                [
                    [
                        'admin.widgets.dashboard.usage',
                        'admin.widgets.dashboard.notification',
                    ],
                    ['admin.widgets.dashboard.chart'],
                ],
            ],
        ];
        $grid = $filter->apply('grid:admin.index', $grid);

        return view('admin.index', [
            'grid' => $grid,
            'sum' => [
                'users' => User::count(),
                'players' => Player::count(),
                'textures' => Texture::count(),
                'storage' => Texture::select('size')->sum('size'),
            ],
        ]);
    }

    public function chartData()
    {
        $xAxis = Collection::times(31, fn ($i) => Carbon::today()->subDays(31 - $i)->format('m-d'));

        $oneMonthAgo = Carbon::today()->subMonth();

        $grouping = fn ($field) => fn ($item) => substr($item->$field, 5, 5);
        $mapping = fn ($item) => count($item);
        $aligning = fn ($data) => fn ($day) => ($data->get($day) ?? 0);

        $userRegistration = User::where('register_at', '>=', $oneMonthAgo)
            ->select('register_at')
            ->get()
            ->groupBy($grouping('register_at'))
            ->map($mapping);

        $textureUploads = Texture::where('upload_at', '>=', $oneMonthAgo)
            ->select('upload_at')
            ->get()
            ->groupBy($grouping('upload_at'))
            ->map($mapping);

        return [
            'labels' => [
                trans('admin.index.user-registration'),
                trans('admin.index.texture-uploads'),
            ],
            'xAxis' => $xAxis,
            'data' => [
                $xAxis->map($aligning($userRegistration)),
                $xAxis->map($aligning($textureUploads)),
            ],
        ];
    }

    public function status(
        Request $request,
        PluginManager $plugins,
        Filesystem $filesystem,
        Filter $filter
    ) {
        $db = config('database.connections.'.config('database.default'));
        $dbType = Arr::get([
            'mysql' => 'MySQL/MariaDB',
            'sqlite' => 'SQLite',
            'pgsql' => 'PostgreSQL',
        ], config('database.default'), '');

        $enabledPlugins = $plugins->getEnabledPlugins()->map(fn ($plugin) => [
            'title' => trans($plugin->title), 'version' => $plugin->version,
        ]);

        if ($filesystem->exists(base_path('.git'))) {
            $process = new \Symfony\Component\Process\Process(
                ['git', 'log', '--pretty=%H', '-1']
            );
            $process->run();
            $commit = $process->isSuccessful() ? trim($process->getOutput()) : '';
        }

        $grid = [
            'layout' => [
                ['md-6', 'md-6'],
            ],
            'widgets' => [
                [
                    ['admin.widgets.status.info'],
                    ['admin.widgets.status.plugins'],
                ],
            ],
        ];
        $grid = $filter->apply('grid:admin.status', $grid);

        return view('admin.status')
            ->with('grid', $grid)
            ->with('detail', [
                'bs' => [
                    'version' => config('app.version'),
                    'env' => config('app.env'),
                    'debug' => config('app.debug') ? trans('general.yes') : trans('general.no'),
                    'commit' => Str::limit(
                        $commit ?? resolve(\App\Services\Webpack::class)->commit,
                        16,
                        ''
                    ),
                    'laravel' => app()->version(),
                ],
                'server' => [
                    'php' => PHP_VERSION,
                    'web' => $request->server('SERVER_SOFTWARE', trans('general.unknown')),
                    'os' => sprintf('%s %s %s', php_uname('s'), php_uname('r'), php_uname('m')),
                ],
                'db' => [
                    'type' => $dbType,
                    'host' => Arr::get($db, 'host', ''),
                    'port' => Arr::get($db, 'port', ''),
                    'username' => Arr::get($db, 'username'),
                    'database' => Arr::get($db, 'database'),
                    'prefix' => Arr::get($db, 'prefix'),
                ],
            ])
            ->with('plugins', $enabledPlugins);
    }
}
