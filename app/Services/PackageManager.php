<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use ZipArchive;

class PackageManager
{
    protected $path;

    /** @var Client */
    protected $guzzle;

    /** @var Filesystem */
    protected $filesystem;

    /** @var ZipArchive */
    protected $zipper;

    public function __construct(
        Client $guzzle,
        Filesystem $filesystem,
        ZipArchive $zipper
    ) {
        $this->guzzle = $guzzle;
        $this->filesystem = $filesystem;
        $this->zipper = $zipper;
    }

    public function download(string $url, string $path, $shasum = null): self
    {
        $this->path = $path;
        try {
            $this->guzzle->request('GET', $url, [
                'sink' => $path,
                'verify' => \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath(),
            ]);
        } catch (Exception $e) {
            throw new Exception(trans('admin.download.errors.download', ['error' => $e->getMessage()]));
        }

        if (is_string($shasum) && sha1_file($path) !== strtolower($shasum)) {
            $this->filesystem->delete($path);
            throw new Exception(trans('admin.download.errors.shasum'));
        }

        return $this;
    }

    public function extract(string $destination): void
    {
        $zip = $this->zipper;
        $resource = $zip->open($this->path);

        if ($resource === true && $zip->extractTo($destination)) {
            $zip->close();
            $this->filesystem->delete($this->path);
        } else {
            throw new Exception(trans('admin.download.errors.unzip'));
        }
    }
}
