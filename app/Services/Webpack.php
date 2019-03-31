<?php

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

    public function __get($path)
    {
        return url('app').'/'.Arr::get($this->manifest, $path, '');
    }
}
