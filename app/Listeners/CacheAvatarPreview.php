<?php

namespace App\Listeners;

use App\Services\Minecraft;
use Cache;
use Storage;

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

        return response($content, 200, ['Content-Type' => 'image/png']);
    }
}
