<?php

namespace App\Console\Commands;

use App\Services\PluginManager;
use Illuminate\Console\Command;

class PluginEnableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:enable {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable a plugin';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PluginManager $plugins)
    {
        $plugin = $plugins->get($this->argument('name'));
        if ($plugin) {
            $plugins->enable($this->argument('name'));
            $this->info(trans('admin.plugins.operations.enabled', ['plugin' => $plugin->title]));
        } else {
            $this->warn(trans('admin.plugins.operations.not-found'));
        }
    }
}
