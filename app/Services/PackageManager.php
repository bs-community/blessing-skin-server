<?php

namespace App\Services;

use Cache;
use Exception;

class PackageManager
{
    protected $guzzle;
    protected $path;
    protected $cacheKey;
    protected $onProgress;

    public function __construct(\GuzzleHttp\Client $guzzle)
    {
        $this->guzzle = $guzzle;
        $this->onProgress = function ($total, $done) {
            Cache::put($this->cacheKey, serialize(['total' => $total, 'done' => $done]));
        };
    }

    public function download($url, $path, $shasum = null)
    {
        $this->path = $path;
        $this->cacheKey = "download_$url";
        Cache::forget($this->cacheKey);
        try {
            $this->guzzle->request('GET', $url, [
                'sink' => $path,
                'progress' => $this->onProgress,
            ]);
        } catch (Exception $e) {
            throw new Exception(trans('admin.download.errors.download', ['error' => $e->getMessage()]));
        }

        Cache::forget($this->cacheKey);
        if (is_string($shasum) && sha1_file($path) != $shasum) {
            @unlink($path);
            throw new Exception(trans('admin.download.errors.shasum'));
        }

        return $this;
    }

    public function extract($destination)
    {
        $zip = new \ZipArchive();
        $resource = $zip->open($this->path);

        if ($resource === true && $zip->extractTo($destination)) {
            $zip->close();
            @unlink($this->path);
        } else {
            throw new Exception(trans('admin.download.errors.unzip'));
        }
    }

    public function progress()
    {
        $progress = unserialize(Cache::get($this->cacheKey));
        if ($progress['total'] == 0) {
            return 0;
        } else {
            return $progress['done'] / $progress['total'];
        }
    }
}
