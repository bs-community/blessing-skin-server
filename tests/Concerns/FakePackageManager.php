<?php

namespace Tests\Concerns;

class FakePackageManager extends \App\Services\PackageManager
{
    private $throw;

    public function __construct(\GuzzleHttp\Client $guzzle = null, bool $throw = false)
    {
        $this->guzzle = $guzzle;
        $this->throw = $throw;
    }

    public function download($url, $path, $shasum = null)
    {
        if ($this->throw) {
            throw new \Exception('');
        } else {
            return $this;
        }
    }

    public function extract($destination)
    {
        return true;
    }

    public function progress()
    {
        return '0';
    }
}
