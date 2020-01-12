<?php

namespace App\Providers;

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
            'App\Listeners\ResetInvalidTextureForPlayer',
        ],
        'App\Events\TextureDeleting' => [
            'App\Listeners\TextureRemoved',
        ],
        'App\Events\PluginWasEnabled' => [
            'App\Listeners\CopyPluginAssets',
            'App\Listeners\GeneratePluginTranslations',
        ],
        'plugin.versionChanged' => [
            'App\Listeners\CopyPluginAssets',
            'App\Listeners\GeneratePluginTranslations',
        ],
        'App\Events\PluginBootFailed' => [
            'App\Listeners\NotifyFailedPlugin',
        ],
        'App\Events\RenderingHeader' => [
            'App\Listeners\SerializeGlobals',
        ],
    ];
}
