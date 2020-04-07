<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \Laravel\Passport\Console\KeysCommand::class,
        Commands\BsInstallCommand::class,
        Commands\OptionsCacheCommand::class,
        Commands\PluginDisableCommand::class,
        Commands\PluginEnableCommand::class,
        Commands\SaltRandomCommand::class,
        Commands\UpdateCommand::class,
    ];
}
