<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Laravel\Passport\Console\KeysCommand::class,
        Commands\KeyRandomCommand::class,
        Commands\SaltRandomCommand::class,
        Commands\MigratePlayersTable::class,
        Commands\MigrateCloset::class,
        Commands\ExecuteInstallation::class,
        Commands\RegressLikesField::class,
    ];
}
