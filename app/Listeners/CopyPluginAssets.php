<?php

namespace App\Listeners;

use Illuminate\Filesystem\Filesystem;

class CopyPluginAssets
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function handle($event)
    {
        $plugin = $event->plugin;
        $dir = public_path('plugins/'.$plugin->name);
        $this->filesystem->deleteDirectory($dir);

        $this->filesystem->copyDirectory(
            $plugin->getPath().DIRECTORY_SEPARATOR.'assets',
            $dir.'/assets'
        );
        $this->filesystem->copyDirectory(
            $plugin->getPath().DIRECTORY_SEPARATOR.'lang',
            $dir.'/lang'
        );
    }
}
