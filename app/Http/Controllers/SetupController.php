<?php

namespace App\Http\Controllers;

use DB;
use Log;
use File;
use Utils;
use Schema;
use Option;
use Storage;
use Artisan;
use App\Models\User;
use Illuminate\Http\Request;
use Composer\Semver\Comparator;
use Illuminate\Validation\Validator;
use App\Exceptions\PrettyPageException;

class SetupController extends Controller
{
    public function database(Request $request)
    {
        config([
            'database.connections.temp.driver' => $request->input('type'),
            'database.connections.temp.host' => $request->input('host'),
            'database.connections.temp.port' => $request->input('port'),
            'database.connections.temp.username' => $request->input('username'),
            'database.connections.temp.password' => $request->input('password'),
            'database.connections.temp.database' => $request->input('db') == '' ? null : $request->input('db'),
            'database.connections.temp.prefix' => $request->input('prefix') == '' ? null : $request->input('prefix'),
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

        $content = File::get('.env');
        $content = str_replace(
            'DB_CONNECTION = '.env('DB_CONNECTION'),
            'DB_CONNECTION = '.$request->input('type'),
            $content
        );
        $content = str_replace(
            'DB_HOST = '.env('DB_HOST'),
            'DB_HOST = '.$request->input('host'),
            $content
        );
        $content = str_replace(
            'DB_PORT = '.env('DB_PORT'),
            'DB_PORT = '.$request->input('port'),
            $content
        );
        $content = str_replace(
            'DB_DATABASE = '.env('DB_DATABASE'),
            'DB_DATABASE = '.$request->input('db'),
            $content
        );
        $content = str_replace(
            'DB_USERNAME = '.env('DB_USERNAME'),
            'DB_USERNAME = '.$request->input('username'),
            $content
        );
        $content = str_replace(
            'DB_PASSWORD = '.env('DB_PASSWORD'),
            'DB_PASSWORD = '.$request->input('password'),
            $content
        );
        $content = str_replace(
            'DB_PREFIX = '.env('DB_PREFIX'),
            'DB_PREFIX = '.$request->input('prefix'),
            $content
        );
        File::put('.env', $content);

        return redirect('setup/info');
    }

    public function info()
    {
        $existingTables = static::checkTablesExist([], true);

        // Not installed completely
        if (count($existingTables) > 0) {
            Log::info('Remaining tables detected, exit setup wizard now', [$existingTables]);

            $existingTables = array_map(function ($item) {
                return get_db_config()['prefix'].$item;
            }, $existingTables);

            throw new PrettyPageException(trans('setup.database.table-already-exists', ['tables' => json_encode($existingTables)]), 1);
        }

        // @codeCoverageIgnoreStart
        if (! function_exists('escapeshellarg')) {
            throw new PrettyPageException(trans('setup.disabled-functions.escapeshellarg'), 1);
        }
        // @codeCoverageIgnoreEnd

        return view('setup.wizard.info');
    }

    public function finish(Request $request)
    {
        $data = $this->validate($request, [
            'email'     => 'required|email',
            'nickname'  => 'required|no_special_chars|max:255',
            'password'  => 'required|min:8|max:32|confirmed',
            'site_name' => 'required'
        ]);

        if ($request->has('generate_random')) {
            // Generate new APP_KEY & SALT randomly
            if (is_writable(app()->environmentFile())) {
                Artisan::call('key:random');
                Artisan::call('salt:random');
            } else {
                // @codeCoverageIgnoreStart
                Log::warning("[SetupWizard] Failed to set application key. No write permission.");
                // @codeCoverageIgnoreEnd
            }
        }

        // Create tables
        Artisan::call('migrate', ['--force' => true]);
        Log::info("[SetupWizard] Tables migrated.");

        Option::set('site_name', $request->input('site_name'));

        $siteUrl = url('/');

        if (ends_with($siteUrl, '/index.php')) {
            $siteUrl = substr($siteUrl, 0, -10);    // @codeCoverageIgnore
        }

        Option::set('site_url',  $siteUrl);

        // Register super admin
        $user = new User;
        $user->email = $data['email'];
        $user->nickname = $data['nickname'];
        $user->score = option('user_initial_score');
        $user->avatar = 0;
        $user->password = User::getEncryptedPwdFromEvent($data['password'], $user)
            ?: app('cipher')->hash($data['password'], config('secure.salt'));
        $user->ip = Utils::getClientIp();
        $user->permission = User::SUPER_ADMIN;
        $user->register_at = Utils::getTimeFormatted();
        $user->last_sign_at = Utils::getTimeFormatted(time() - 86400);

        $user->save();

        $this->createDirectories();

        return view('setup.wizard.finish')->with([
            'email'    => $request->input('email'),
            'password' => $request->input('password')
        ]);
    }

    public function update()
    {
        if (Comparator::lessThanOrEqualTo(config('app.version'), option('version'))) {
            // No updates available
            return view('setup.locked');
        }

        return view('setup.updates.welcome');
    }

    public function doUpdate()
    {
        $resource = opendir(database_path('update_scripts'));
        $updateScriptExist = false;

        $tips = [];

        while($filename = @readdir($resource)) {
            if ($filename != "." && $filename != "..") {
                preg_match('/update-(.*)-to-(.*).php/', $filename, $matches);

                // Skip if the file is not valid or expired
                if (! isset($matches[2]) ||
                    Comparator::lessThan($matches[2], config('app.version'))) {
                    continue;
                }

                $result = require database_path('update_scripts')."/$filename";

                if (is_array($result)) {
                    // Push the tip into array
                    foreach ($result as $tip) {
                        $tips[] = $tip;
                    }
                }

                $updateScriptExist = true;
            }
        }
        closedir($resource);

        foreach (config('options') as $key => $value) {
            if (! Option::has($key)) {
                Option::set($key, $value);
            }
        }

        if (! $updateScriptExist) {
            // If there is no update script given
            Option::set('version', config('app.version'));
        }

        // Clear all compiled view files
        try {
            Artisan::call('view:clear');
        } catch (\Exception $e) {
            Log::error('Error occured when processing view:clear', [$e]);

            $files = collect(File::files(storage_path('framework/views')));
            $files->reject(function ($path) {
                return ends_with($path, '.gitignore');
            })->each(function ($path) {
                File::delete($path);
            });
        }

        return view('setup.updates.success', ['tips' => $tips]);
    }

    /**
     * Check if the given tables exist in current database.
     *
     * @param  array $tables
     * @param  bool  $returnExisting
     * @return bool|array
     */
    public static function checkTablesExist($tables = [], $returnExistingTables = false) {
        $existingTables = [];
        $tables = $tables ?: ['users', 'closets', 'players', 'textures', 'options'];

        foreach ($tables as $tableName) {
            // Table prefix will be added automatically
            if (Schema::hasTable($tableName)) {
                $existingTables[] = $tableName;
            }
        }

        if (count($existingTables) == count($tables)) {
            return true;
        } else {
            return $returnExistingTables ? $existingTables : false;
        }
    }

    public static function checkDirectories()
    {
        $directories = ['storage/textures', 'plugins'];

        try {
            foreach ($directories as $dir) {
                if (! Storage::disk('root')->has($dir)) {
                    // Try to mkdir
                    if (! Storage::disk('root')->makeDirectory($dir)) {
                        return false;
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function createDirectories()
    {
        return self::checkDirectories();
    }
}
