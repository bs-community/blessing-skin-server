<?php

namespace App\Providers;

use View;
use Utils;
use Schema;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use App\Exceptions\PrettyPageException;

class BootServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        View::addExtension('tpl', 'blade');

        $this->checkFileExists();
        $this->checkDbConfig();

        if (!$request->is('setup') && !$request->is('setup/*') && PHP_SAPI != "cli") {
            $this->checkInstallation();
        }
    }

    protected function checkFileExists()
    {
        if (!file_exists(base_path('.env'))) {
            throw new PrettyPageException(trans('setup.file.no-dot-env'), -1);
        }
    }

    protected function checkDbConfig()
    {
        $config = config('database.connections.mysql');

        // use error control to hide shitty connect warnings
        @$conn = new \mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $config['port']
        );

        if ($conn->connect_error)
            throw new PrettyPageException(
                trans('setup.database.connection-error', ['msg' => $conn->connect_error]),
                $conn->connect_errno
            );

        return true;
    }

    protected function checkInstallation()
    {
        // redirect to setup wizard
        if (!$this->checkTablesExist()) {
            return redirect('/setup')->send();
        }

        Utils::checkTextureDirectory();

        if (version_compare(config('app.version'), option('version', ''), '>')) {
            return redirect('/setup/update')->send();
        }

        return true;
    }

    public static function checkTablesExist()
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
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('database', \App\Services\Database\Database::class);
        $this->app->singleton('options', \App\Services\Repositories\OptionRepository::class);
    }
}
