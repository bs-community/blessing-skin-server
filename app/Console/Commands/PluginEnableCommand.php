<?php

namespace App\Console\Commands;

use App\Services\PluginManager;
use Illuminate\Console\Command;

class PluginEnableCommand extends Command
{
    protected $signature = 'plugin:enable {name}';

    protected $description = 'Enable a plugin';

    public function handle(PluginManager $plugins)
    {
        $name = $this->argument('name');
        $result = $plugins->enable($name);
        if ($result === true) {
            $plugin = $plugins->get($name);
            $this->info(trans('admin.plugins.operations.enabled', ['plugin' => $plugin->title]));
        } elseif ($result === false) {
            $this->warn(trans('admin.plugins.operations.not-found'));
        }
    }
}
