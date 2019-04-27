<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ExecuteInstallation extends Command
{
    protected $signature = 'bs:install {email} {password} {nickname}';

    protected $description = 'Execute installation and create a super administrator.';

    public function handle()
    {
        if (\App\Http\Controllers\SetupController::checkTablesExist()) {
            $this->info('You have installed Blessing Skin Server. Nothing to do.');

            return;
        }

        if (config('app.env') != 'testing') {
            $this->call('key:random');
        }
        $this->call('salt:random');
        $this->call('migrate', ['--force' => true]);
        $this->call('jwt:secret', ['--no-interaction' => true]);
        $this->call('passport:keys', ['--no-interaction' => true]);

        $siteUrl = url('/');
        if (ends_with($siteUrl, '/index.php')) {
            $siteUrl = substr($siteUrl, 0, -10);
        }
        option(['site_url' => $siteUrl]);

        $admin = new User;
        $admin->email = $this->argument('email');
        $admin->nickname = $this->argument('nickname');
        $admin->score = option('user_initial_score');
        $admin->avatar = 0;
        $admin->password = app('cipher')->hash($this->argument('password'), config('secure.salt'));
        $admin->ip = '127.0.0.1';
        $admin->permission = User::SUPER_ADMIN;
        $admin->register_at = get_datetime_string();
        $admin->last_sign_at = get_datetime_string(time() - 86400);
        $admin->verified = true;
        $admin->save();

        $this->info('Installation completed!');
        $this->info('We recommend to modify your "Site URL" option if incorrect.');
    }
}
