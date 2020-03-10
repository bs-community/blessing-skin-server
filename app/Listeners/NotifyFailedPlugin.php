<?php

namespace App\Listeners;

use Event;
use App\Models\User;

class NotifyFailedPlugin
{
    public function handle($event)
    {
        $plugin = $event->plugin;
        Event::listen(\App\Events\RenderingFooter::class, function ($event) use ($plugin) {
            /** @var User */
            $user = auth()->user();
            if ($user && $user->isAdmin()) {
                $message = trans('errors.plugins.boot', ['plugin' => trans($plugin->title)]);
                $event->addContent("<script>blessing.notify.toast.error('$message')</script>");
            }
        });
    }
}
