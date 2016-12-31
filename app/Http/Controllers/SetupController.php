<?php

namespace App\Http\Controllers;

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

    public function update()
    {
        if (version_compare(config('app.version'), option('version', ''), '<=')) {
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
                    version_compare($matches[2], config('app.version'), '<')) {
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

        return view('setup.updates.success', ['tips' => $tips]);
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

        // create tables
        Artisan::call('migrate', ['--force' => true]);

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

        $this->createDirectories();

        return view('setup.wizard.finish')->with([
            'email'    => $request->input('email'),
            'password' => $request->input('password')
        ]);
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

    public static function checkTextureDirectory()
    {
        if (!Storage::disk('storage')->has('textures')) {
            // mkdir
            if (!Storage::disk('storage')->makeDirectory('textures'))
                return false;
        }

        return true;
    }

    protected function createDirectories()
    {
        return self::checkTextureDirectory();
    }

    /**
     * {@inheritdoc}
     */
    protected function formatValidationErrors(Validator $validator)
    {
        return $validator->errors()->all();
    }

}
