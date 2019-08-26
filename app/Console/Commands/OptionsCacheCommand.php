<?php

namespace App\Console\Commands;

use App\Services\Option;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;

class OptionsCacheCommand extends Command
{
    protected $signature = 'options:cache';

    protected $description = 'Cache Blessing Skin options';

    public function handle(Filesystem $filesystem, Application $app)
    {
        $path = storage_path('options/cache.php');
        $filesystem->delete($path);
        $app->forgetInstance(Option::class);

        $content = var_export(resolve(Option::class)->all(), true);
        $content = '<?php'.PHP_EOL.'return '.$content.';';
        $filesystem->put($path, $content);
        $this->info('Options cached successfully.');
    }
}
