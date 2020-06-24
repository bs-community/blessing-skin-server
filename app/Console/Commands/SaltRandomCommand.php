<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SaltRandomCommand extends Command
{
    protected $signature = 'salt:random {--show : Display the salt instead of modifying files}';

    protected $description = 'Set the application salt';

    public function handle()
    {
        $salt = $this->generateRandomSalt();

        if ($this->option('show')) {
            return $this->line('<comment>'.$salt.'</comment>');
        }

        // Next, we will replace the application salt in the environment file so it is
        // automatically setup for this developer. This salt gets generated using a
        // secure random byte generator and is later base64 encoded for storage.
        $this->setKeyInEnvironmentFile($salt);

        $this->laravel['config']['secure.salt'] = $salt;

        $this->info("Application salt [$salt] set successfully.");
    }

    protected function setKeyInEnvironmentFile(string $salt)
    {
        file_put_contents($this->laravel->environmentFilePath(), str_replace(
            'SALT = '.$this->laravel['config']['secure.salt'],
            'SALT = '.$salt,
            file_get_contents($this->laravel->environmentFilePath())
        ));
    }

    protected function generateRandomSalt(): string
    {
        return bin2hex(resolve(\Illuminate\Contracts\Encryption\Encrypter::class)->generateKey('AES-128-CBC'));
    }
}
