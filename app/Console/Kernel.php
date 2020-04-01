<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \Laravel\Passport\Console\KeysCommand::class,
    ];

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
