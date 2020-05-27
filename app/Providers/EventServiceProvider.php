<?php

namespace App\Providers;

use App\Listeners;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\PlayerRetrieved' => [
            Listeners\ResetInvalidTextureForPlayer::class,
        ],
        'App\Events\TextureDeleting' => [
            'App\Listeners\TextureRemoved',
        ],
        'App\Events\PluginWasEnabled' => [
            Listeners\CopyPluginAssets::class,
            Listeners\GeneratePluginTranslations::class,
        ],
        'plugin.versionChanged' => [
            'App\Listeners\CopyPluginAssets',
            'App\Listeners\GeneratePluginTranslations',
        ],
        'App\Events\PluginBootFailed' => [
            Listeners\NotifyFailedPlugin::class,
        ],
        'App\Events\RenderingHeader' => [
            Listeners\SerializeGlobals::class,
        ],
        'player.name.updated' => [
            Listeners\SinglePlayer\UpdateOwnerNickName::class,
        ],
        'auth.registration.completed' => [
            Listeners\SendEmailVerification::class,
        ],
    ];
}
