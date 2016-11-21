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
        if ($this->checkTablesExist()) {
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
        $update_script_exist = false;

        $tips = [];

        while($filename = @readdir($resource)) {
            if ($filename != "." && $filename != "..") {
                preg_match('/update-(.*)-to-(.*).php/', $filename, $matches);

                // skip if the file is not valid or expired
                if (!isset($matches[2]) ||
                    version_compare($matches[2], option('version'), '<')) {
                    continue;
                }

                $result = require database_path('update_scripts')."/$filename";

                if (is_array($result)) {
                    // push tip to array
                    foreach ($result as $tip) {
                        $tips[] = $tip;
                    }
                }

                $update_script_exist = true;
            }
        }
        closedir($resource);

        foreach (config('options') as $key => $value) {
            if (!Option::has($key))
                Option::set($key, $value);
        }

        if (!$update_script_exist) {
            // if update script is not given
            Option::set('version', config('app.version'));
        }

        return view('setup.updates.success', ['tips' => $tips]);
    }

    public function info()
    {
        return view('setup.wizard.info');
    }

    public function finish(Request $request)
    {
        $this->validate($request, [
            'email'     => 'required|email',
            'password'  => 'required|min:6|max:16|confirmed',
            'site_name' => 'required'
        ]);

        // create tables
        Artisan::call('migrate');

        Option::set('site_name', $request->input('site_name'));
        Option::set('site_url',  url('/'));

        // register super admin
        $user = User::register(
            $request->input('email'),
            $request->input('password'),
            function ($user)
        {
            $user->ip           = get_real_ip();
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

    protected function createDirectories()
    {
        Utils::checkTextureDirectory();
    }

    protected function checkTablesExist()
    {
        $tables = ['users', 'closets', 'players', 'textures', 'options'];

        foreach ($tables as $table_name) {
            // prefix will be added automatically
            if (!Schema::hasTable($table_name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function formatValidationErrors(Validator $validator)
    {
        return $validator->errors()->all();
    }

}
