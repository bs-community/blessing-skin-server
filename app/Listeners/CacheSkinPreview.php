<?php

namespace App\Listeners;

use App\Services\Minecraft;
use Cache;
use Storage;

class CacheSkinPreview
{
    public function handle($event)
    {
        $texture = $event->texture;
        $size = $event->size;
        $key = "preview-{$texture->tid}-{$size}";

        $content = Cache::rememberForever($key, function () use ($texture, $size) {
            $res = Storage::disk('textures')->read($texture->hash);

            if ($texture->type == 'cape') {
                $png = Minecraft::generatePreviewFromCape($res, $size * 0.8, $size * 1.125, $size);
            } else {
                $png = Minecraft::generatePreviewFromSkin($res, $size, $texture->type == 'alex', 'both', 4);
            }

            return png($png);
        });

        return response($content, 200, [
            'Content-Type' => 'image/png',
            'Last-Modified' => Storage::disk('textures')->lastModified($texture->hash),
        ]);
    }
}
