<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Webpack
{
    protected array $manifest = [];

    protected Option $options;

    protected UrlGenerator $urlGenerator;

    public function __construct(
        Filesystem $filesystem,
        Option $options,
        UrlGenerator $urlGenerator
    ) {
        $path = public_path('app/manifest.json');
        if ($filesystem->exists($path)) {
            $this->manifest = json_decode($filesystem->get($path), true);
        }

        $this->options = $options;
        $this->urlGenerator = $urlGenerator;
    }

    public function __get(string $path)
    {
        return Arr::get($this->manifest, $path, '');
    }

    public function url(string $path): string
    {
        if (Str::startsWith(config('app.asset.env'), 'dev')) {
            $root = config('app.asset.url').':8080';

            return $this->urlGenerator->assetFrom($root, $path);
        } else {
            $path = $this->$path;
            $cdn = $this->options->get('cdn_address');

            return $this->urlGenerator->assetFrom($cdn, "/app/$path");
        }
    }
}
