<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\GetSkinPreview' => [
            'App\Listeners\CacheSkinPreview',
        ],
        'App\Events\GetAvatarPreview' => [
            'App\Listeners\CacheAvatarPreview',
        ],
        'App\Events\GetPlayerJson' => [
            'App\Listeners\CachePlayerJson',
        ],
        'App\Events\PlayerProfileUpdated' => [
            'App\Listeners\FreshPlayerJson',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}
