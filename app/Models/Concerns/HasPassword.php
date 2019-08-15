<?php

namespace App\Models\Concerns;

use Illuminate\Support\Arr;
use App\Events\EncryptUserPassword;

trait HasPassword
{
    public function verifyPassword($raw)
    {
        // Compare directly if any responses is returned by event dispatcher
        if ($result = $this->getEncryptedPwdFromEvent($raw, $this)) {
            return hash_equals($this->password, $result);     // @codeCoverageIgnore
        }

        return app('cipher')->verify($raw, $this->password, config('secure.salt'));
    }

    /**
     * Try to get encrypted password from event dispatcher.
     *
     * @param  string $raw
     * @return mixed
     */
    public function getEncryptedPwdFromEvent($raw)
    {
        $responses = event(new EncryptUserPassword($raw, $this));

        return Arr::get($responses, 0);
    }

    /**
     * Change password of the user.
     *
     * @param  string $password New password that will be set.
     * @return bool
     */
    public function changePassword($password)
    {
        $responses = event(new EncryptUserPassword($password, $this));
        $this->password = Arr::get($responses, 0, app('cipher')->hash($password, config('secure.salt')));

        return $this->save();
    }
}
