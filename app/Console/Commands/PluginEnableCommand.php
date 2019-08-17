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
