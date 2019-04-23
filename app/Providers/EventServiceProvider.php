<?php

namespace App\Providers;

use Event;
use App\Events;
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
            'App\Listeners\ResetInvalidTextureForPlayer',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        if (option('enable_avatar_cache')) {
            Event::listen(Events\GetAvatarPreview::class, Listeners\CacheAvatarPreview::class);
        }
        if (option('enable_preview_cache')) {
            Event::listen(Events\GetSkinPreview::class, Listeners\CacheSkinPreview::class);
        }
        if (option('enable_notfound_cache')) {
            Event::subscribe(Listeners\CachePlayerExists::class);
        }
        if (option('enable_json_cache')) {
            Event::subscribe(Listeners\CachePlayerJson::class);
        }
    }
}
