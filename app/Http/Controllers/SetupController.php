<?php

namespace App\Http\Controllers;

use App\Exceptions\PrettyPageException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel as Artisan;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Vectorface\Whip\Whip;

class SetupController extends Controller
{
    public function database(
        Request $request,
        Filesystem $filesystem,
        Connection $connection,
        DatabaseManager $manager
    ) {
        if ($request->isMethod('get')) {
            try {
                $connection->getPdo();

                return redirect('setup/info');
            } catch (\Exception $e) {
                return view('setup.wizard.database', [
                    'host' => env('DB_HOST'),
                    'port' => env('DB_PORT'),
                    'username' => env('DB_USERNAME'),
                    'password' => env('DB_PASSWORD'),
                    'database' => env('DB_DATABASE'),
                    'prefix' => env('DB_PREFIX'),
                ]);
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
            $manager->connection('temp')->getPdo();
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $type = Arr::get([
                'mysql' => 'MySQL/MariaDB',
                'sqlite' => 'SQLite',
                'pgsql' => 'PostgreSQL',
            ], $request->input('type'), '');

            throw new PrettyPageException(trans('setup.database.connection-error', compact('msg', 'type')), $e->getCode());
        }

        $content = $filesystem->get(base_path('.env'));
        $content = preg_replace(
            '/DB_CONNECTION.+/',
            'DB_CONNECTION='.$request->input('type', ''),
            $content
        );
        $content = preg_replace(
            '/DB_HOST.+/',
            'DB_HOST='.$request->input('host', ''),
            $content
        );
        $content = preg_replace(
            '/DB_PORT.+/',
            'DB_PORT='.$request->input('port', ''),
            $content
        );
        $content = preg_replace(
            '/DB_DATABASE.+/',
            'DB_DATABASE='.$request->input('db', ''),
            $content
        );
        $content = preg_replace(
            '/DB_USERNAME.+/',
            'DB_USERNAME='.$request->input('username', ''),
            $content
        );
        $content = preg_replace(
            '/DB_PASSWORD.+/',
            'DB_PASSWORD='.$request->input('password', ''),
            $content
        );
        $content = preg_replace(
            '/DB_PREFIX.+/',
            'DB_PREFIX='.$request->input('prefix', ''),
            $content
        );
        $filesystem->put(base_path('.env'), $content);

        return redirect('setup/info');
    }

    public function finish(Request $request, Filesystem $filesystem, Artisan $artisan)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'nickname' => 'required',
            'password' => 'required|min:8|max:32|confirmed',
            'site_name' => 'required',
        ]);

        $artisan->call('passport:keys', ['--no-interaction' => true]);

        // Create tables
        $artisan->call('migrate', [
            '--force' => true,
            '--path' => [
                'database/migrations',
                'vendor/laravel/passport/database/migrations',
            ],
          ]);

        $siteUrl = url('/');
        if (Str::endsWith($siteUrl, '/index.php')) {
            $siteUrl = substr($siteUrl, 0, -10);    // @codeCoverageIgnore
        }
        option([
            'site_name' => $request->input('site_name'),
            'site_url' => $siteUrl,
        ]);

        $whip = new Whip();
        $ip = $whip->getValidIpAddress();

        // Register super admin
        $user = new User();
        $user->email = $data['email'];
        $user->nickname = $data['nickname'];
        $user->score = option('user_initial_score');
        $user->avatar = 0;
        $user->password = app('cipher')->hash($data['password'], config('secure.salt'));
        $user->ip = $ip;
        $user->permission = User::SUPER_ADMIN;
        $user->register_at = Carbon::now();
        $user->last_sign_at = Carbon::now()->subDay();
        $user->verified = true;

        $user->save();

        $filesystem->put(storage_path('install.lock'), '');

        return view('setup.wizard.finish');
    }
}
