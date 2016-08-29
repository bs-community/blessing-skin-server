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

        $path = BASE_DIR."/storage/textures/$hash";

        if (!\Storage::disk('cache')->has("avatar/$tid")) {
            $hash = Texture::find($tid)->hash;
            $filename = BASE_DIR."/storage/textures/$hash";

            $png = \Minecraft::generateAvatarFromSkin($filename, $event->size);
            imagepng($png, BASE_DIR."/storage/cache/avatar/$tid");
            imagedestroy($png);
        }

    }
}
