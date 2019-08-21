<?php

namespace App\Console\Commands;

use App\Services\Option;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class OptionsCacheCommand extends Command
{
    protected $signature = 'options:cache';

    protected $description = 'Cache Blessing Skin options';

    /** @var Filesystem */
    protected $filesystem;

    /** @var Option */
    protected $options;

    public function __construct(Filesystem $filesystem, Option $options)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->options = $options;
    }

    public function handle()
    {
        $content = var_export($this->options->all(), true);
        $content = '<?php'.PHP_EOL.'return '.$content.';';
        $this->filesystem->put(storage_path('options/cache.php'), $content);
        $this->info('Options cached successfully.');
    }
}
