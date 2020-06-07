<?php

namespace App\Providers;

use App\Listeners;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    // The event listener mappings for the application.
    protected $listen = [
        'App\Events\PlayerRetrieved' => [
            Listeners\ResetInvalidTextureForPlayer::class,
        ],
        'App\Events\PluginWasEnabled' => [
            Listeners\CopyPluginAssets::class,
            Listeners\GeneratePluginTranslations::class,
        ],
        'plugin.versionChanged' => [
            Listeners\CopyPluginAssets::class,
            Listeners\GeneratePluginTranslations::class,
        ],
        'App\Events\PluginBootFailed' => [
            Listeners\NotifyFailedPlugin::class,
        ],
        'App\Events\RenderingHeader' => [
            Listeners\SerializeGlobals::class,
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
    ];
}
