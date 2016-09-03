<?php

namespace App\Listeners;

use App\Models\Texture;
use App\Events\GetAvatarPreview;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CacheAvatarPreview
{
    /**
     * Handle the event.
     *
     * @param  GetAvatarPreview  $event
     * @return void
     */
    public function handle(GetAvatarPreview $event)
    {
        $tid  = $event->texture->tid;
        $hash = $event->texture->hash;
        $size = $event->size;

        $path = BASE_DIR."/storage/textures/$hash";

        if (!\Storage::disk('cache')->has("avatar/$tid-$size")) {
            $png = \Minecraft::generateAvatarFromSkin($path, $event->size);
            imagepng($png, BASE_DIR."/storage/cache/avatar/$tid-$size");
            imagedestroy($png);
        }

        return \Response::png(\Storage::disk('cache')->get("avatar/$tid-$size"));
    }
}
