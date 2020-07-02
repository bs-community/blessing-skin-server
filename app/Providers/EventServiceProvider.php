<?php

namespace App\Providers;

use App\Listeners;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    // The event listener mappings for the application.
    protected $listen = [
        'App\Events\PluginWasEnabled' => [
            Listeners\CopyPluginAssets::class,
        ],
        'plugin.versionChanged' => [
            Listeners\CopyPluginAssets::class,
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
}
