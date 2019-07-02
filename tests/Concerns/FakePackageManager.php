<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Services\PackageManager;

class FakePackageManager extends PackageManager
{
    private $throw;

    public function __construct(\GuzzleHttp\Client $guzzle = null, bool $throw = false)
    {
        $this->guzzle = $guzzle;
        $this->throw = $throw;
    }

    public function download(string $url, string $path, $shasum = null): PackageManager
    {
        if ($this->throw) {
            throw new \Exception('');
        } else {
            return $this;
        }
    }

    public function extract(string $destination): void
    {
        //
    }

    public function progress(): float
    {
        return 0.0;
    }
}
