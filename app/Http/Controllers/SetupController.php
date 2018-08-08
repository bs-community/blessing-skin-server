<?php

namespace App\Http\Controllers;

use Log;
use File;
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
    public function welcome()
    {
        $type = get_db_type();

        if ($type === 'SQLite') {
            // @codeCoverageIgnoreStart
            $server = get_db_config()['database'];
            // @codeCoverageIgnoreEnd
        } else {
            $config = get_db_config();
            $server = "{$config['username']}@{$config['host']}";
        }

        return view('setup.wizard.welcome')->with(compact('type', 'server'));
    }

    public function info()
    {
        $existingTables = static::checkTablesExist([], true);

        // Not installed completely
        if (count($existingTables) > 0) {
            Log::info('[SetupWizard] Remaining tables detected, exit now', [$existingTables]);

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
        $this->validate($request, [
            'email'     => 'required|email',
            'password'  => 'required|min:8|max:32|confirmed',
            'site_name' => 'required'
        ]);

        if ($request->has('generate_random')) {
            // Generate new APP_KEY & SALT randomly
            if (is_writable(app()->environmentFile())) {
                Artisan::call('key:random');
                Artisan::call('salt:random');

                Log::info('[SetupWizard] Random application key & salt set successfully');
            } else {
                // @codeCoverageIgnoreStart
                Log::warning('[SetupWizard] Failed to set application key since .env is not writable');
                // @codeCoverageIgnoreEnd
            }
        }

        // Create tables
        Artisan::call('migrate', ['--force' => true]);
        Log::info('[SetupWizard] Database migrated');

        Option::set('site_name', $request->get('site_name'));

        $siteUrl = url('/');

        if (ends_with($siteUrl, '/index.php')) {
            $siteUrl = substr($siteUrl, 0, -10);    // @codeCoverageIgnore
        }

        Option::set('site_url',  $siteUrl);

        // Register super admin
        $user = User::register(
            $request->get('email'),
            $request->get('password'), function ($user)
        {
            $user->ip           = get_client_ip();
            $user->score        = option('user_initial_score');
            $user->register_at  = get_datetime_string();
            $user->last_sign_at = get_datetime_string(time() - 86400);
            $user->permission   = User::SUPER_ADMIN;
        });
        Log::info('[SetupWizard] Super administrator registered');

        $this->createDirectories();
        Log::info('[SetupWizard] Installation completed');

        return view('setup.wizard.finish')->with([
            'email'    => $request->get('email'),
            'password' => $request->get('password')
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
            Log::error('[UpdateWizard] Error occured when processing view:clear', [$e]);

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
    public static function checkTablesExist($tables = [], $returnExistingTables = false)
    {
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

    /**
     * Check if the given columns exist in specific table.
     * By default, we will check the columns newly added to users table in BS v3.5.0.
     *
     * @param string $table
     * @param array  $columns
     * @return void
     */
    public static function checkNewColumnsExist($table = 'users', $columns = [])
    {
        $existingColumns = [];
        $columns = $columns ?: ['verified', 'verification_token'];

        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                $existingColumns[] = $column;
            }
        }

        return count($existingColumns) === count($columns);
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

    /**
     * {@inheritdoc}
     */
    protected function formatValidationErrors(Validator $validator)
    {
        return $validator->errors()->all();
    }
}
