<?php

namespace App\Http\Controllers;

use DB;
use Log;
use File;
use Option;
use Artisan;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Composer\Semver\Comparator;
use Illuminate\Filesystem\Filesystem;
use App\Exceptions\PrettyPageException;
use Symfony\Component\Finder\SplFileInfo;

class SetupController extends Controller
{
    public function welcome(Filesystem $filesystem)
    {
        if (! $filesystem->exists(base_path('.env'))) {
            $filesystem->copy(base_path('.env.example'), base_path('.env'));
        }

        return view('setup.wizard.welcome');
    }

    public function database(Request $request)
    {
        if ($request->isMethod('get')) {
            try {
                DB::getPdo();

                return redirect('setup/info');
                // @codeCoverageIgnoreStart
            } catch (\Exception $e) {
                return view('setup.wizard.database');
                // @codeCoverageIgnoreEnd
            }
        }

        config([
            'database.connections.temp.driver' => $request->input('type'),
            'database.connections.temp.host' => $request->input('host'),
            'database.connections.temp.port' => $request->input('port'),
            'database.connections.temp.username' => $request->input('username'),
            'database.connections.temp.password' => $request->input('password'),
            'database.connections.temp.database' => $request->input('db'),
            'database.connections.temp.prefix' => $request->input('prefix'),
        ]);

        try {
            DB::connection('temp')->getPdo();
        } catch (\Exception $e) {
            $msg = iconv('gbk', 'utf-8', $e->getMessage());
            $type = humanize_db_type($request->input('type'));

            throw new PrettyPageException(
                trans('setup.database.connection-error', compact('msg', 'type')),
                $e->getCode()
            );
        }

        $content = File::get(base_path('.env'));
        $content = preg_replace(
            '/DB_CONNECTION.+/',
            'DB_CONNECTION = '.$request->input('type'),
            $content
        );
        $content = preg_replace(
            '/DB_HOST.+/',
            'DB_HOST = '.$request->input('host'),
            $content
        );
        $content = preg_replace(
            '/DB_PORT.+/',
            'DB_PORT = '.$request->input('port'),
            $content
        );
        $content = preg_replace(
            '/DB_DATABASE.+/',
            'DB_DATABASE = '.$request->input('db'),
            $content
        );
        $content = preg_replace(
            '/DB_USERNAME.+/',
            'DB_USERNAME = '.$request->input('username'),
            $content
        );
        $content = preg_replace(
            '/DB_PASSWORD.+/',
            'DB_PASSWORD = '.$request->input('password'),
            $content
        );
        $content = preg_replace(
            '/DB_PREFIX.+/',
            'DB_PREFIX = '.$request->input('prefix'),
            $content
        );
        File::put(base_path('.env'), $content);

        return redirect('setup/info');
    }

    public function finish(Request $request, Filesystem $filesystem)
    {
        $data = $this->validate($request, [
            'email'     => 'required|email',
            'nickname'  => 'required|no_special_chars|max:255',
            'password'  => 'required|min:8|max:32|confirmed',
            'site_name' => 'required',
        ]);

        if ($request->has('generate_random')) {
            Artisan::call('key:generate');
            Artisan::call('salt:random');
        }
        Artisan::call('jwt:secret', ['--no-interaction' => true]);
        Artisan::call('passport:keys', ['--no-interaction' => true]);

        // Create tables
        Artisan::call('migrate', [
            '--force' => true,
            '--path' => [
                'database/migrations',
                'vendor/laravel/passport/database/migrations',
            ],
          ]);
        Log::info('[SetupWizard] Tables migrated.');

        Option::set('site_name', $request->input('site_name'));

        $siteUrl = url('/');

        if (Str::endsWith($siteUrl, '/index.php')) {
            $siteUrl = substr($siteUrl, 0, -10);    // @codeCoverageIgnore
        }

        Option::set('site_url', $siteUrl);

        // Register super admin
        $user = new User;
        $user->email = $data['email'];
        $user->nickname = $data['nickname'];
        $user->score = option('user_initial_score');
        $user->avatar = 0;
        $user->password = app('cipher')->hash($data['password'], config('secure.salt'));
        $user->ip = get_client_ip();
        $user->permission = User::SUPER_ADMIN;
        $user->register_at = get_datetime_string();
        $user->last_sign_at = get_datetime_string(time() - 86400);
        $user->verified = true;

        $user->save();

        $filesystem->put(storage_path('install.lock'), '');

        return view('setup.wizard.finish')->with([
            'email'    => $request->input('email'),
            'password' => $request->input('password'),
        ]);
    }

    public function update(Filesystem $filesystem)
    {
        collect($filesystem->files(database_path('update_scripts')))
            ->filter(function (SplFileInfo $file) {
                $name = $file->getFilenameWithoutExtension();
                return preg_match('/^\d+\.\d+\.\d+$/', $name) > 0
                    && Comparator::greaterThanOrEqualTo($name, option('version'));
            })
            ->each(function (SplFileInfo $file) use ($filesystem) {
                $filesystem->getRequire($file->getPathname());
            });

        option(['version' => config('app.version')]);
        Artisan::call('view:clear');
        $filesystem->put(storage_path('install.lock'), '');

        return view('setup.updates.success');
    }
}
