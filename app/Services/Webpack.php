<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class Webpack
{
    protected $manifest = [];

    /** @var Option */
    protected $options;

    public function __construct(Filesystem $filesystem, Option $options)
    {
        $path = public_path('app/manifest.json');
        if ($filesystem->exists($path)) {
            $this->manifest = json_decode($filesystem->get($path), true);
        }

        $this->options = $options;
    }

    public function __get(string $path)
    {
        return Arr::get($this->manifest, $path, '');
    }

    public function url(string $path): string
    {
        if (Str::startsWith(config('app.asset.env'), 'dev')) {
            $base = config('app.asset.url');

            return "$base:8080/$path";
        } else {
            $path = $this->$path;
            $cdn = $this->options->get('cdn_address');

            return $cdn ? "$cdn/app/$path" : url("/app/$path");
        }
    }
}
