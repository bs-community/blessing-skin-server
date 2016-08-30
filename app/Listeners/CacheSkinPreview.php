<?php

namespace App\Listeners;

use Storage;
use App\Events\GetSkinPreview;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CacheSkinPreview
{
    /**
     * Handle the event.
     *
     * @param  GetSkinPreview  $event
     * @return void
     */
    public function handle(GetSkinPreview $event)
    {
        $tid = $event->texture->tid;

        if (!Storage::disk('cache')->has("preview/$tid")) {
            $filename = BASE_DIR."/storage/textures/{$event->texture->hash}";

            if ($event->texture->type == "cape") {
                $png = \Minecraft::generatePreviewFromCape($filename, $event->size);
                imagepng($png, BASE_DIR."/storage/cache/preview/$tid");
                imagedestroy($png);
            } else {
                $png = \Minecraft::generatePreviewFromSkin($filename, $event->size);
                imagepng($png, BASE_DIR."/storage/cache/preview/$tid");
                imagedestroy($png);
            }
        }

        return \Response::png(Storage::disk('cache')->get("preview/$tid"));
    }
}
