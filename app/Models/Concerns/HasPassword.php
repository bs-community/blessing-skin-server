<?php

namespace App\Models\Concerns;

use App\Events\EncryptUserPassword;
use Illuminate\Support\Arr;

trait HasPassword
{
    public function verifyPassword(string $raw)
    {
        // Compare directly if any responses is returned by event dispatcher
        if ($result = $this->getEncryptedPwdFromEvent($raw, $this)) {
            return hash_equals($this->password, $result);     // @codeCoverageIgnore
        }

        return app('cipher')->verify($raw, $this->password, config('secure.salt'));
    }

    /**
     * Try to get encrypted password from event dispatcher.
     */
    public function getEncryptedPwdFromEvent(string $raw)
    {
        $responses = event(new EncryptUserPassword($raw, $this));

        return Arr::get($responses, 0);
    }

    public function changePassword(string $password): bool
    {
        $responses = event(new EncryptUserPassword($password, $this));
        $hash = Arr::get($responses, 0);
        if (empty($hash)) {
            $hash = app('cipher')->hash($password, config('secure.salt'));
        }
        $this->password = $hash;

        return $this->save();
    }
}
