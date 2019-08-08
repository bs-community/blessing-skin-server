<?php

namespace App\Console\Commands;

use App\Services\PluginManager;
use Illuminate\Console\Command;

class DisablePlugin extends Command
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
        $plugins->disable($this->argument('name'));
    }
}
