<?php

namespace App\Providers;

use DB;
use Illuminate\Http\Request;
use Composer\Semver\Comparator;
use Illuminate\Support\ServiceProvider;
use App\Exceptions\PrettyPageException;
use App\Http\Controllers\SetupController;

class RuntimeCheckServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        // Detect current locale
        $this->app->call('App\Http\Middleware\DetectLanguagePrefer@detect');

        $this->checkFilePermissions();
        $this->checkDatabaseConnection();

        // Skip the installation check on setup wizard or under CLI
        if ($request->is('setup*') || $this->app->runningInConsole()) {
            return;
        }

        // Redirect to setup wizard if not installed
        $this->checkInstallation();
    }

    protected function checkFilePermissions()
    {
        // Check dotenv file
        if (! file_exists(app()->environmentFile())) {
            throw new PrettyPageException(trans('setup.file.no-dot-env'), -1);
        }

        if (! is_readable(app()->environmentFile())) {
            throw new PrettyPageException(trans('setup.file.dot-env-no-read-permission'), -1);
        }

        // Check permissions of storage path
        if (! is_writable(storage_path())) {
            throw new PrettyPageException(trans('setup.permissions.storage'), -1);
        }

        if (! SetupController::checkDirectories()) {
            throw new PrettyPageException(trans('setup.file.permission-error'), -1);
        }
    }

    protected function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            if ($this->app->runningInConsole()) {
                // Dump some useful information for debugging
                dump([
                    'APP_ENV' => app()->environment(),
                    'DOTENV_FILE' => app()->environmentFile(),
                    'DB_CONNECTION' => get_db_config()
                ]);
            }

            $msg = iconv('gbk', 'utf-8', $e->getMessage());
            $type = get_db_type();

            throw new PrettyPageException(
                trans('setup.database.connection-error', compact('msg', 'type')),
                $e->getCode()
            );
        }
    }

    protected function checkInstallation()
    {
        // Redirect to setup wizard
        if (! SetupController::checkTablesExist()) {
            return redirect('/setup')->send();
        }

        if (Comparator::greaterThan(config('app.version'), option('version'))) {
            return redirect('/setup/update')->send();
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
        //
    }
}
