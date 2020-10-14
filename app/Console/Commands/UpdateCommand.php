<?php

namespace App\Console\Commands;

use Composer\Semver\Comparator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as Artisan;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;

class UpdateCommand extends Command
{
    protected $signature = 'update';

    protected $description = 'Execute update.';

    public function handle(Artisan $artisan, Filesystem $filesystem)
    {
        $this->procedures()->each(function ($procedure, $version) {
            if (Comparator::lessThan(option('version'), $version)) {
                $procedure();
            }
        });

        option(['version' => config('app.version')]);
        $artisan->call('migrate', ['--force' => true]);
        $artisan->call('view:clear');
        $filesystem->put(storage_path('install.lock'), '');
        Cache::flush();

        $this->info(trans('setup.updates.success.title'));
    }

    /**
     * @codeCoverageIgnore
     */
    protected function procedures()
    {
        return collect([
            // this is just for testing
            '0.0.1' => fn () => event('__0.0.1'),
            '5.0.0' => function () {
                if (option('home_pic_url') === './app/bg.jpg') {
                    option(['home_pic_url' => './app/bg.webp']);
                }
            },
        ]);
    }
}
