<?php

namespace App\Listeners;

use App\Services\Plugin;
use Illuminate\Filesystem\Filesystem;

class CopyPluginAssets
{
    protected Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function handle($event)
    {
        $plugin = $event instanceof Plugin ? $event : $event->plugin;
        $dir = public_path('plugins/'.$plugin->name);
        $this->filesystem->deleteDirectory($dir);

        $this->filesystem->copyDirectory(
            $plugin->getPath().DIRECTORY_SEPARATOR.'assets',
            $dir.'/assets'
        );
    }
}
