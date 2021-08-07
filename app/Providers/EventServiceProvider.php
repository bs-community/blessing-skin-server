<?php

namespace App\Providers;

use App\Listeners;
use App\Models\Scope;
use App\Observers\ScopeObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    // The event listener mappings for the application.
    protected $listen = [
        'App\Events\PluginWasEnabled' => [
            Listeners\CopyPluginAssets::class,
            Listeners\CleanUpFrontEndLocaleFiles::class,
        ],
        'plugin.versionChanged' => [
            Listeners\CopyPluginAssets::class,
            Listeners\CleanUpFrontEndLocaleFiles::class,
        ],
        'App\Events\PluginBootFailed' => [
            Listeners\NotifyFailedPlugin::class,
        ],
        'auth.registration.completed' => [
            Listeners\SendEmailVerification::class,
        ],
        'texture.privacy.updated' => [
            Listeners\ResetPlayers::class,
            Listeners\CleanUpCloset::class,
        ],
        'texture.deleted' => [
            Listeners\UpdateScoreForDeletedTexture::class,
            Listeners\ResetPlayers::class,
            Listeners\CleanUpCloset::class,
        ],
        'closet.removed' => [
            Listeners\ResetPlayerForRemovedClosetItem::class,
        ],
        'Illuminate\Auth\Events\Authenticated' => [
            Listeners\SetAppLocale::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Scope::observe(ScopeObserver::class);
    }
}
