<?php

namespace App\Listeners;

use Event;

class NotifyFailedPlugin
{
    public function handle($event)
    {
        $plugin = $event->plugin;
        Event::listen(\App\Events\RenderingFooter::class, function ($event) use ($plugin) {
            $user = auth()->user();
            if ($user && $user->isAdmin()) {
                $options = json_encode([
                    'type' => 'error',
                    'message' => trans('errors.plugins.boot', ['plugin' => trans($plugin->title)]),
                    'duration' => 0,
                    'showClose' => true,
                ]);
                $event->addContent('<script>blessing.ui.message('.$options.')</script>');
            }
        });
    }
}
