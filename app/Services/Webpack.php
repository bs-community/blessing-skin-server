<?php

declare(strict_types=1);

namespace App\Services;

use File;
use Illuminate\Support\Arr;

class Webpack
{
    protected $manifest = [];

    public function __construct()
    {
        $path = public_path('app/manifest.json');
        if (File::exists($path)) {
            $this->manifest = json_decode(File::get($path), true);
        }
    }

    public function __get(string $path)
    {
        return Arr::get($this->manifest, $path, '');
    }
}
