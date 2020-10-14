<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Filesystem\Filesystem;
use ZipArchive;

class Unzip
{
    protected Filesystem $filesystem;

    protected ZipArchive $zipper;

    public function __construct(Filesystem $filesystem, ZipArchive $zipper)
    {
        $this->filesystem = $filesystem;
        $this->zipper = $zipper;
    }

    public function extract(string $file, string $destination): void
    {
        $zip = $this->zipper;
        $resource = $zip->open($file);

        if ($resource === true && $zip->extractTo($destination)) {
            $zip->close();
            $this->filesystem->delete($file);
        } else {
            throw new Exception(trans('admin.download.errors.unzip'));
        }
    }
}
