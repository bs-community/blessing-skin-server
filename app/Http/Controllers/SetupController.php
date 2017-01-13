<?php

namespace App\Http\Controllers;

use Log;
use Utils;
use Schema;
use Option;
use Storage;
use Artisan;
use Database;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use App\Exceptions\PrettyPageException;

class SetupController extends Controller
{
    public function __construct(Request $request)
    {
        if ($locale = $request->input('lang')) {
            cookie()->queue('locale', $locale);
            session(['locale' => $locale]);
            app()->setLocale($locale);
        }
    }

    public function welcome()
    {
        // already installed
        if (self::checkTablesExist()) {
            return view('setup.locked');
        } else {
            $config = config('database.connections.mysql');

            return view('setup.wizard.welcome')->with('server', "{$config['username']}@{$config['host']}");
        }
    }

    public function info()
    {
        if (self::checkTablesExist()) {
            return view('setup.locked');
        }

        return view('setup.wizard.info');
    }

    public function finish(Request $request)
    {
        if (self::checkTablesExist()) {
            return view('setup.locked');
        }

        $this->validate($request, [
            'email'     => 'required|email',
            'password'  => 'required|min:6|max:16|confirmed',
            'site_name' => 'required'
        ]);

        if (isset($_POST['generate_random'])) {
            // generate new APP_KEY & SALT randomly
            if (is_writable(app()->environmentFile())) {
                Artisan::call('key:random');
                Artisan::call('salt:random');

                Log::info("[SetupWizard] Random application key & salt set successfully.", [
                    'key'  => config('app.key'),
                    'salt' => config('secure.salt')
                ]);
            } else {
                Log::warning("[SetupWizard] Failed to set application key. No write permission.");
            }
        }

        // create tables
        Artisan::call('migrate', ['--force' => true]);
        Log::info("[SetupWizard] Tables migrated.");

        Option::set('site_name', $request->input('site_name'));
        Option::set('site_url',  url('/'));

        // register super admin
        $user = User::register(
            $request->input('email'),
            $request->input('password'), function ($user)
        {
            $user->ip           = Utils::getClientIp();
            $user->score        = option('user_initial_score');
            $user->register_at  = Utils::getTimeFormatted();
            $user->last_sign_at = Utils::getTimeFormatted(time() - 86400);
            $user->permission   = User::SUPER_ADMIN;
        });
        Log::info("[SetupWizard] Super Admin registered.", ['user' => $user]);

        $this->createDirectories();
        Log::info("[SetupWizard] Installation completed.");

        return view('setup.wizard.finish')->with([
            'email'    => $request->input('email'),
            'password' => $request->input('password')
        ]);
    }

    public function update()
    {
        if (Utils::versionCompare(config('app.version'), option('version', ''), '<=')) {
            // no updates available
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

                // skip if the file is not valid or expired
                if (!isset($matches[2]) ||
                    Utils::versionCompare($matches[2], config('app.version'), '<')) {
                    continue;
                }

                $result = require database_path('update_scripts')."/$filename";

                if (is_array($result)) {
                    // push tip to array
                    foreach ($result as $tip) {
                        $tips[] = $tip;
                    }
                }

                $updateScriptExist = true;
            }
        }
        closedir($resource);

        foreach (config('options') as $key => $value) {
            if (!Option::has($key))
                Option::set($key, $value);
        }

        if (!$updateScriptExist) {
            // if update script is not given
            Option::set('version', config('app.version'));
        }

        // clear all compiled view files
        Artisan::call('view:clear');

        return view('setup.updates.success', ['tips' => $tips]);
    }

    /**
     * Check if the given tables exist in current database.
     *
     * @param  array $tables
     * @return bool
     */
    public static function checkTablesExist($tables = [
        'users', 'closets', 'players', 'textures', 'options'
    ]) {
        $totalTables = 0;

        foreach ($tables as $tableName) {
            // prefix will be added automatically
            if (Schema::hasTable($tableName)) {
                $totalTables++;
            }
        }

        if ($totalTables == count($tables)) {
            return true;
        } else {
            // not installed completely
            foreach (array_merge($tables, ['migrations']) as $tableName) {
                Schema::dropIfExists($tableName);
            }
            return false;
        }
    }

    public static function checkDirectories()
    {
        $directories = ['storage/textures', 'plugins'];

        try {
            foreach ($directories as $dir) {
                if (!Storage::disk('root')->has($dir)) {
                    // mkdir
                    if (!Storage::disk('root')->makeDirectory($dir))
                        return false;
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
