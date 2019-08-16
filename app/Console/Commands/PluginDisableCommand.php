<?php

namespace App\Console\Commands;

use App\Services\PluginManager;
use Illuminate\Console\Command;

class PluginDisableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:disable {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable a plugin';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PluginManager $plugins)
    {
        $plugin = $plugins->get($this->argument('name'));
        if ($plugin) {
            $plugins->disable($this->argument('name'));
            $this->info(trans('admin.plugins.operations.disabled', ['plugin' => $plugin->title]));
        } else {
            $this->warn(trans('admin.plugins.operations.not-found'));
        }
    }
}
