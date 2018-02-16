<?php

namespace App\Providers;

use View;
use Utils;
use App\Services\Database;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use App\Exceptions\PrettyPageException;
use App\Http\Controllers\SetupController;
use App\Services\Repositories\OptionRepository;

class BootServiceProvider extends ServiceProvider
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

        // Skip the installation check when setup or under CLI
        if (! $request->is('setup*') && PHP_SAPI != "cli") {
            $this->checkInstallation();
        }
    }

    protected function checkFilePermissions()
    {
        // Check dotenv file
        if (! file_exists(app()->environmentFile())) {
            throw new PrettyPageException(trans('setup.file.no-dot-env'), -1);
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
            // Check database config
            Database::prepareConnection();
        } catch (\Exception $e) {
            if ($this->app->runningInConsole()) {
                // Dump some useful information for debugging
                dump([
                    'APP_ENV' => app()->environment(),
                    'DOTENV_FILE' => app()->environmentFile(),
                    'DB_CONNECTION' => config('database.connections.mysql')
                ]);
            }

            throw new PrettyPageException(
                trans('setup.database.connection-error', ['msg' => $e->getMessage()]),
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

        if (Utils::versionCompare(config('app.version'), option('version', ''), '>')) {
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
        View::addExtension('tpl', 'blade');

        $this->app->singleton('options',  OptionRepository::class);
        $this->app->singleton('database', Database::class);
    }
}
