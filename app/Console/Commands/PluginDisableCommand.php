<?php

namespace App\Console\Commands;

use App\Services\PluginManager;
use Illuminate\Console\Command;

class PluginDisableCommand extends Command
{
    protected $signature = 'plugin:disable {name}';

    protected $description = 'Disable a plugin';

    public function handle(PluginManager $plugins)
    {
        $plugin = $plugins->get($this->argument('name'));
        if ($plugin) {
            $plugins->disable($this->argument('name'));
            $title = trans($plugin->title);
            $this->info(trans('admin.plugins.operations.disabled', ['plugin' => $title]));
        } else {
            $this->warn(trans('admin.plugins.operations.not-found'));
        }
    }
}
