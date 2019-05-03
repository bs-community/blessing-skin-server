<?php

\Illuminate\Console\Application::starting(function ($artisan) {
    $artisan->resolveCommands([\Laravel\Passport\Console\KeysCommand::class]);
});

$tips = [];

Artisan::call('jwt:secret', ['--no-interaction' => true]);
Artisan::call('migrate', ['--force' => true]);
try {
    Artisan::call('passport:keys', ['--no-interaction' => true]);
} catch (\Exception $e) {
    $tips[] = nl2br(implode("\n", [
        '您需要打开终端或 PowerShell 来执行这条命令：<code>php artisan passport:keys</code>',
        'You need to open terminal or PowerShell and execute: <code>php artisan passport:keys</code>'
    ]));
}

return $tips;
