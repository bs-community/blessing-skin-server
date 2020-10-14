<?php

namespace App\Listeners;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class CleanUpFrontEndLocaleFiles
{
    protected Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function handle()
    {
        $files = $this->filesystem->allFiles(public_path('lang'));
        array_walk($files, function (SplFileInfo $file) {
            if ($file->getExtension() === 'js') {
                $this->filesystem->delete($file->getPathname());
            }
        });
    }
}
