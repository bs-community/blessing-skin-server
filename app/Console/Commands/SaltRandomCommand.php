<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SaltRandomCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salt:random {--show : Display the salt instead of modifying files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the application salt';

    /**
     * Execute the console command.
     *
     * @return void
     */
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

    /**
     * Set the application salt in the environment file.
     *
     * @param  string  $salt
     * @return void
     */
    protected function setKeyInEnvironmentFile($salt)
    {
        file_put_contents($this->laravel->environmentFilePath(), str_replace(
            'SALT = '.$this->laravel['config']['secure.salt'],
            'SALT = '.$salt,
            file_get_contents($this->laravel->environmentFilePath())
        ));
    }

    /**
     * Generate a random salt for the application.
     *
     * @return string
     */
    protected function generateRandomSalt()
    {
        return bin2hex(resolve(\Illuminate\Contracts\Encryption\Encrypter::class)->generateKey('AES-128-CBC'));
    }
}
