<?php

declare(strict_types=1);

namespace App\Services;

use Cache;
use Exception;
use ZipArchive;
use Illuminate\Filesystem\Filesystem;

class PackageManager
{
    protected $guzzle;
    protected $path;
    protected $cacheKey;
    protected $onProgress;

    /** @var Filesystem */
    protected $filesystem;

    /** @var ZipArchive */
    protected $zipper;

    public function __construct(
        \GuzzleHttp\Client $guzzle,
        Filesystem $filesystem,
        ZipArchive $zipper
    ) {
        $this->guzzle = $guzzle;
        $this->filesystem = $filesystem;
        $this->zipper = $zipper;
        $this->onProgress = function ($total, $done) {
            Cache::put($this->cacheKey, serialize(['total' => $total, 'done' => $done]));
        };
    }

    public function download(string $url, string $path, $shasum = null): self
    {
        $this->path = $path;
        $this->cacheKey = "download_$url";
        Cache::forget($this->cacheKey);
        try {
            $this->guzzle->request('GET', $url, [
                'sink' => $path,
                'progress' => $this->onProgress,
                'verify' => \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath(),
            ]);
        } catch (Exception $e) {
            throw new Exception(trans('admin.download.errors.download', ['error' => $e->getMessage()]));
        }

        Cache::forget($this->cacheKey);
        if (is_string($shasum) && sha1_file($path) != strtolower($shasum)) {
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

    public function progress(): float
    {
        $progress = unserialize(Cache::get($this->cacheKey));
        if ($progress['total'] == 0) {
            return 0;
        } else {
            return $progress['done'] / $progress['total'];
        }
    }
}
