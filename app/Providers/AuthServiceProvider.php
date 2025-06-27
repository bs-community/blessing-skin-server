<?php

namespace App\Providers;

use App\Models\Scope;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $defaultScopes = [
            'User.Read' => 'auth.oauth.scope.user.read',
            'Notification.Read' => 'auth.oauth.scope.notification.read',
            'Notification.ReadWrite' => 'auth.oauth.scope.notification.readwrite',
            'Player.Read' => 'auth.oauth.scope.player.read',
            'Player.ReadWrite' => 'auth.oauth.scope.player.readwrite',
            'Closet.Read' => 'auth.oauth.scope.closet.read',
            'Closet.ReadWrtie' => 'auth.oauth.scope.closet.readwrite',
            'UsersManagement.Read' => 'auth.oauth.scope.users-management.read',
            'UsersManagement.ReadWrite' => 'auth.oauth.scope.users-management.readwrite',
            'PlayersManagement.Read' => 'auth.oauth.scope.players-management.read',
            'PlayersManagement.ReadWrite' => 'auth.oauth.scope.players-management.readwrite',
            'ClosetManagement.Read' => 'auth.oauth.scope.closet-management.read',
            'ClosetManagement.ReadWrite' => 'auth.oauth.scope.closet-management.readwrite',
            'ReportsManagement.Read' => 'auth.oauth.scope.reports-management.read',
            'ReportsManagement.ReadWrite' => 'auth.oauth.scope.reports-management.readwrite',
        ];

        /*
         * Return empty scopes if running unit tests or before installation.
         * In these cases, migrations aren’t run yet, so DB queries will fail.
         * OAuth isn’t tested in unit tests, so returning empty scopes should be fine...?
         * Maybe the best approach is to run migrations before bootstrap in tests,
         * but this seems impossible for DB_DATABASE=:memory:;
         * Or change how scopes are registered so they don't depend on the database,
         * but that may introduce BREAKING CHANGES and plugin incompatibility.
         * PRs welcome for better solutions!
         */
        $scopes = (app()->runningUnitTests() || !Storage::disk('root')->exists('storage/install.lock')) ? [] : Cache::rememberForever('scopes', function () {
            return Scope::pluck('description', 'name')->toArray();
        });

        Passport::tokensCan(array_merge($defaultScopes, $scopes));

        Passport::setDefaultScope(['User.Read']);
    }
}
