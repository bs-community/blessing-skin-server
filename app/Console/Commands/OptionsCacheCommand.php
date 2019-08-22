<?php

namespace App\Console\Commands;

use App\Services\Option;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class OptionsCacheCommand extends Command
{
    protected $signature = 'options:cache';

    protected $description = 'Cache Blessing Skin options';

    public function handle(Filesystem $filesystem, Option $options)
    {
        $content = var_export($options->all(), true);
        $content = '<?php'.PHP_EOL.'return '.$content.';';
        $filesystem->put(storage_path('options/cache.php'), $content);
        $this->info('Options cached successfully.');
    }
}
