<?php

namespace App\Models\Concerns;

use App\Services\Cipher\BaseCipher;
use Blessing\Filter;

trait HasPassword
{
    public function verifyPassword(string $raw)
    {
        /** @var BaseCipher */
        $cipher = resolve('cipher');
        /** @var Filter */
        $filter = resolve(Filter::class);
        $password = $this->password;
        $user = $this;

        $passed = $cipher->verify($raw, $password, config('secure.salt'));
        $passed = $filter->apply('verify_password', $passed, [$raw, $user]);

        return $passed;
    }

    public function changePassword(string $password): bool
    {
        $password = resolve('cipher')->hash($password, config('secure.salt'));
        $password = resolve(Filter::class)->apply('user_password', $password);
        $this->password = $password;

        return $this->save();
    }
}
