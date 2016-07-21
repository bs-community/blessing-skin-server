<?php

namespace App\Services;

class Minecraft
{
    /**
     * Cut and resize to get avatar from skin, HD support by <xfl03@hotmail.com>
     *
     * @author https://github.com/jamiebicknell/Minecraft-Avatar/blob/master/face.php
     * @param  string $resource, img path or base64
     * @param  int    $size
     * @param  string $view, default for 'f'
     * @param  bool   $base64, if given $resource is encoded in base64
     * @return resource
     */
    public static function generateAvatarFromSkin($resource, $size, $view='f', $base64 = false)
    {
        $src = $base64 ? imagecreatefromstring(base64_decode($resource)) : imagecreatefrompng($resource);
        $dest = imagecreatetruecolor($size, $size);
        $ratio = imagesx($src) / 64; // width/64

        // f => front, l => left, r => right, b => back
        $x = array('f' => 8, 'l' => 16, 'r' => 0, 'b' => 24);

        imagecopyresized($dest, $src, 0, 0, $x[$view] * $ratio, 8 * $ratio, $size, $size, 8 * $ratio, 8 * $ratio);         // Face
        imagecolortransparent($src, imagecolorat($src, 63 * $ratio, 0));                                                   // Black Hat Issue
        imagecopyresized($dest, $src, 0, 0, ($x[$view] + 32) * $ratio, 8 * $ratio, $size, $size, 8 * $ratio, 8 * $ratio);  // Accessories

        imagedestroy($src);
        return $dest;
    }

    /**
     * Generate skin preview
     *
     * @see    https://github.com/NC22/Minecraft-HD-skin-viewer-2D/blob/master/SkinViewer2D.class.php
     * @param  resource       $resource
     * @param  int            $size
     * @param  boolean|string $side, 'front' or 'back'
     * @param  boolean        $base64, generate image from base64 string
     * @return resource
     */
    public static function generatePreviewFromSkin($resource, $size, $side = false, $base64 = false)
    {
        $src = $base64 ? imagecreatefromstring(base64_decode($resource)) : imagecreatefrompng($resource);

        $ratio = imagesx($src) / 64;
        $dest = imagecreatetruecolor((($side) ? 16 : 36) * $ratio, 32 * $ratio);
        $half_width = ($side) ? 0 : imagesx($dest) / 2 + 2 * $ratio;

        $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
        imagefill($dest, 0, 0, $transparent);

        if (!$side or $side === 'front') {
            imagecopy($dest, $src, 4 * $ratio, 0 * $ratio, 8 * $ratio, 8 * $ratio, 8 * $ratio, 8 * $ratio);
            imagecopy($dest, $src, 0 * $ratio, 8 * $ratio, 44 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio);
            self::imageflip($dest, $src, 12 * $ratio, 8 * $ratio, 44 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio);
            imagecopy($dest, $src, 4 * $ratio, 8 * $ratio, 20 * $ratio, 20 * $ratio, 8 * $ratio, 12 * $ratio);
            imagecopy($dest, $src, 4 * $ratio, 20 * $ratio, 4 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio);
            self::imageflip($dest, $src, 8 * $ratio, 20 * $ratio, 4 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio);
            imagecopy($dest, $src, 4 * $ratio, 0 * $ratio, 40 * $ratio, 8 * $ratio, 8 * $ratio, 8 * $ratio);
        }

        if (!$side or $side === 'back') {
            imagecopy($dest, $src, $half_width + 4 * $ratio, 8 * $ratio, 32 * $ratio, 20 * $ratio, 8 * $ratio, 12 * $ratio);
            imagecopy($dest, $src, $half_width + 4 * $ratio, 0 * $ratio, 24 * $ratio, 8 * $ratio, 8 * $ratio, 8 * $ratio);
            self::imageflip($dest, $src, $half_width + 0 * $ratio, 8 * $ratio, 52 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio);
            imagecopy($dest, $src, $half_width + 12 * $ratio, 8 * $ratio, 52 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio);
            self::imageflip($dest, $src, $half_width + 4 * $ratio, 20 * $ratio, 12 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio);
            imagecopy($dest, $src, $half_width + 8 * $ratio, 20 * $ratio, 12 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio);
            imagecopy($dest, $src, $half_width + 4 * $ratio, 0 * $ratio, 56 * $ratio, 8 * $ratio, 8 * $ratio, 8 * $ratio);
        }

        $size_x = ($side) ? $size / 2 : $size / 32 * 36;
        $fullsize = imagecreatetruecolor($size_x, $size);

        imagesavealpha($fullsize, true);
        $transparent = imagecolorallocatealpha($fullsize, 255, 255, 255, 127);
        imagefill($fullsize, 0, 0, $transparent);

        imagecopyresized($fullsize, $dest, 0, 0, 0, 0, imagesx($fullsize), imagesy($fullsize), imagesx($dest), imagesy($dest));

        imagedestroy($dest);
        imagedestroy($src);

        return $fullsize;
    }

    private static function imageflip(&$result, &$img, $rx = 0, $ry = 0, $x = 0, $y = 0, $size_x = null, $size_y = null)
    {
        if ($size_x < 1)
            $size_x = imagesx($img);
        if ($size_y < 1)
            $size_y = imagesy($img);

        imagecopyresampled($result, $img, $rx, $ry, ($x + $size_x - 1), $y, $size_x, $size_y, 0 - $size_x, $size_y);
    }

    public static function generatePreviewFromCape($resource)
    {
        $src = imagecreatefrompng($resource);

        $dest = imagecreatetruecolor(250, 166);
        imagesavealpha($dest, true);

        $trans_colour = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $trans_colour);

        $src_width = imagesx($src) * 11 / 64;
        $src_height = imagesy($src) * 17 / 32;

        $dst_height = 100;
        // 100 / 17 * 11
        $dst_width = 64;

        // dst_x = (250 - 64) / 2
        imagecopyresized($dest, $src, 93, 30, 0, 0, $dst_width, $dst_height, $src_width, $src_height);

        imagedestroy($src);
        return $dest;
    }
}
