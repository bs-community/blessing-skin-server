<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class BsInstallCommand extends Command
{
    protected $signature = 'bs:install {email} {password} {nickname}';

    protected $description = 'Execute installation and create a super administrator.';

    public function handle(Filesystem $filesystem)
    {
        if ($filesystem->exists(storage_path('install.lock'))) {
            $this->info('You have installed Blessing Skin Server. Nothing to do.');

            return;
        }

        $this->call('migrate', ['--force' => true]);
        if (!$this->getLaravel()->runningUnitTests()) {
            // @codeCoverageIgnoreStart
            $this->call('key:generate');
            $this->call('passport:keys', ['--no-interaction' => true]);
            // @codeCoverageIgnoreEnd
        }

        option(['site_url' => url('/')]);

        $admin = new User();
        $admin->email = $this->argument('email');
        $admin->nickname = $this->argument('nickname');
        $admin->score = option('user_initial_score');
        $admin->avatar = 0;
        $admin->password = app('cipher')->hash($this->argument('password'), config('secure.salt'));
        $admin->ip = '127.0.0.1';
        $admin->permission = User::SUPER_ADMIN;
        $admin->register_at = Carbon::now();
        $admin->last_sign_at = Carbon::now()->subDay();
        $admin->verified = true;
        $admin->save();

        $filesystem->put(storage_path('install.lock'), '');

        $this->info('Installation completed!');
        $this->info('We recommend to modify your "Site URL" option if incorrect.');
    }
}
