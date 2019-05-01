<?php

use Artisan;

\Illuminate\Console\Application::starting(function ($artisan) {
    $artisan->resolveCommands([\Laravel\Passport\Console\KeysCommand::class]);
});

Artisan::call('jwt:secret', ['--no-interaction' => true]);
Artisan::call('migrate', ['--force' => true]);
Artisan::call('passport:keys', ['--no-interaction' => true]);
