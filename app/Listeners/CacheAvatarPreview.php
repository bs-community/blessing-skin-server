<?php

namespace App\Listeners;

use Cache;
use Storage;
use App\Services\Minecraft;

class CacheAvatarPreview
{
    public function handle($event)
    {
        $texture = $event->texture;
        $size = $event->size;
        $key = "avatar-{$texture->tid}-$size";

        $content = Cache::rememberForever($key, function () use ($texture, $size) {
            $res = Storage::disk('textures')->read($texture->hash);

            return png(Minecraft::generateAvatarFromSkin($res, $size));
        });

        return response()->png($content);
    }
}
