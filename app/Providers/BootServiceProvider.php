<?php

namespace App\Providers;

use View;
use Utils;
use Schema;
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
        View::addExtension('tpl', 'blade');

        // check dotenv
        if (!file_exists(base_path('.env'))) {
            throw new PrettyPageException(trans('setup.file.no-dot-env'), -1);
        }

        // check permissions of storage path
        if (!is_writable(storage_path())) {
            throw new PrettyPageException(trans('setup.permissions.storage'), -1);
        }

        try {
            // check database config
            Database::prepareConnection();
        } catch (\Exception $e) {
            throw new PrettyPageException(
                trans('setup.database.connection-error', ['msg' => $e->getMessage()]),
                $e->getCode()
            );
        }

        // skip the installation check when setup or under CLI
        if (!$request->is('setup') && !$request->is('setup/*') && PHP_SAPI != "cli") {
            $this->checkInstallation();
        }
    }

    protected function checkInstallation()
    {
        // redirect to setup wizard
        if (!SetupController::checkTablesExist()) {
            return redirect('/setup')->send();
        }

        if (!SetupController::checkDirectories()) {
            throw new PrettyPageException(trans('setup.file.permission-error'), -1);
        }

        if (version_compare(config('app.version'), option('version', ''), '>')) {
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
        $this->app->singleton('options',  OptionRepository::class);
        $this->app->singleton('database', Database::class);
    }
}
