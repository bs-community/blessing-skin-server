<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Filesystem\Filesystem;

class Webpack
{
    protected $manifest = [];

    public function __construct(Filesystem $filesystem)
    {
        $path = public_path('app/manifest.json');
        if ($filesystem->exists($path)) {
            $this->manifest = json_decode($filesystem->get($path), true);
        }
    }

    public function __get(string $path)
    {
        return Arr::get($this->manifest, $path, '');
    }
}
