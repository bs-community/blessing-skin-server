<?php

namespace App\Services;

use Illuminate\Support\Arr;

class Rejection
{
    /** @var string */
    protected $reason;

    /** @var mixed */
    protected $data;

    public function __construct(string $reason, $data = [])
    {
        $this->reason = $reason;
        $this->data = $data;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getData($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        return Arr::get($this->data, $key, $default);
    }
}
