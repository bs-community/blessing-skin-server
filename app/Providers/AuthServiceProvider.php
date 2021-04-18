<?php

namespace App\Providers;

use App\Models\Scope;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

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

        $scopes = Scope::all()->pluck('description', 'name')->all();

        Passport::tokensCan(array_merge($defaultScopes, $scopes));

        Passport::setDefaultScope(['User.Read']);
    }
}
