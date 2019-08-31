<?php

namespace App\Listeners;

use Event;

class NotifyFailedPlugin
{
    public function handle($event)
    {
        $plugin = $event->plugin;
        $user = auth()->user();
        if ($user && $user->isAdmin()) {
            Event::listen(\App\Events\RenderingFooter::class, function ($event) use ($plugin) {
                $options = json_encode([
                    'type' => 'warning',
                    'title' => trans('errors.plugins.boot.title'),
                    'message' => trans('errors.plugins.boot.message', ['plugin' => trans($plugin->title)]),
                    'duration' => 0,
                ]);
                $event->addContent('<script>blessing.ui.notify('.$options.')</script>');
            });
        }
    }
}
