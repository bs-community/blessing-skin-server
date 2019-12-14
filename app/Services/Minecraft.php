<?php

namespace App\Services;

class Minecraft
{
    /**
     * Cut and resize to get the head part from a skin image.
     * HD skin support added by xfl03 <xfl03@hotmail.com>.
     *
     * @see    https://github.com/jamiebicknell/Minecraft-Avatar/blob/master/face.php
     *
     * @param string $binary binary image data or decoded base64 formatted image
     * @param int    $height the height of generated image in pixel
     * @param string $view   which side of head to be captured, defaults to 'f' for front view
     *
     * @return resource
     */
    public static function generateAvatarFromSkin(string $binary, int $height, string $view = 'f')
    {
        $src = imagecreatefromstring($binary);
        $dest = imagecreatetruecolor($height, $height);
        $ratio = imagesx($src) / 64;

        $x = [
            'f' => 8,  // Front
            'l' => 16, // Left
            'r' => 0,  // Right
            'b' => 24,  // Back
        ];

        imagecopyresized($dest, $src, 0, 0, $x[$view] * $ratio, 8 * $ratio, $height, $height, 8 * $ratio, 8 * $ratio); // Face
        imagecolortransparent($src, imagecolorat($src, 63 * $ratio, 0)); // Black hat issue
        imagecopyresized($dest, $src, 0, 0, ($x[$view] + 32) * $ratio, 8 * $ratio, $height, $height, 8 * $ratio, 8 * $ratio); // Accessories

        imagedestroy($src);

        return $dest;
    }

    /**
     * Generate a image preview for a skin texture.
     *
     * @see    https://github.com/NC22/Minecraft-HD-skin-viewer-2D/blob/master/SkinViewer2D.class.php
     *
     * @param string $binary binary image data or decoded base64 formatted image
     * @param int    $height the height of generated image in pixel
     * @param bool   $alex   whether the given skin is in Alex model
     * @param string $side   which side of model to be captured, 'front', 'back' or 'both'
     * @param int    $gap    gap size between front & back preview in relative pixel
     *
     * @return resource
     */
    public static function generatePreviewFromSkin(string $binary, int $height, $alex = false, $side = 'both', $gap = 4)
    {
        $src = imagecreatefromstring($binary);

        $ratio = imagesx($src) / 64;

        // Check if given skin contains double layers
        $double = imagesy($src) == 64 * $ratio;

        $dest = imagecreatetruecolor((32 + $gap) * $ratio, 32 * $ratio);

        if ($side == 'both') {
            // The width of front view and gap, the back side view will be drawn on its right.
            $half_width = (16 + $gap) * $ratio;
            $dest = imagecreatetruecolor((32 + $gap) * $ratio, 32 * $ratio);
        } else {
            // No need to calculate this if only single side view is required
            $half_width = 0;
            $dest = imagecreatetruecolor((16 + $gap) * $ratio, 32 * $ratio);
        }

        $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
        imagefill($dest, 0, 0, $transparent);

        if ($side == 'both' || $side == 'front') {
            imagecopy($dest, $src, 4 * $ratio, 0 * $ratio, 8 * $ratio, 8 * $ratio, 8 * $ratio, 8 * $ratio); // Head - 1
            imagecopy($dest, $src, 4 * $ratio, 0 * $ratio, 40 * $ratio, 8 * $ratio, 8 * $ratio, 8 * $ratio); // Head - 2
            imagecopy($dest, $src, 4 * $ratio, 8 * $ratio, 20 * $ratio, 20 * $ratio, 8 * $ratio, 12 * $ratio); // Body - 1
            imagecopy($dest, $src, 4 * $ratio, 20 * $ratio, 4 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio); // Right Leg - 1

            if ($alex) {
                imagecopy($dest, $src, 1 * $ratio, 8 * $ratio, 44 * $ratio, 20 * $ratio, 3 * $ratio, 12 * $ratio); // Right Arm - 1
            } else {
                imagecopy($dest, $src, 0 * $ratio, 8 * $ratio, 44 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio); // Right Arm - 1
            }

            // Check if given skin is double layer skin.
            // If not, flip right arm/leg to generate left arm/leg.
            if ($double) {
                imagecopy($dest, $src, 8 * $ratio, 20 * $ratio, 20 * $ratio, 52 * $ratio, 4 * $ratio, 12 * $ratio); // Left Leg - 1

                // copy second layer
                imagecopy($dest, $src, 4 * $ratio, 8 * $ratio, 20 * $ratio, 36 * $ratio, 8 * $ratio, 12 * $ratio); // Body - 2
                imagecopy($dest, $src, 4 * $ratio, 20 * $ratio, 4 * $ratio, 36 * $ratio, 4 * $ratio, 12 * $ratio); // Right Leg - 2
                imagecopy($dest, $src, 8 * $ratio, 20 * $ratio, 4 * $ratio, 52 * $ratio, 4 * $ratio, 12 * $ratio); // Left Leg - 2

                if ($alex) {
                    imagecopy($dest, $src, 12 * $ratio, 8 * $ratio, 36 * $ratio, 52 * $ratio, 3 * $ratio, 12 * $ratio); // Left Arm - 1
                    imagecopy($dest, $src, 1 * $ratio, 8 * $ratio, 44 * $ratio, 36 * $ratio, 3 * $ratio, 12 * $ratio); // Right Arm - 2
                    imagecopy($dest, $src, 11 * $ratio, 8 * $ratio, 50 * $ratio, 52 * $ratio, 3 * $ratio, 12 * $ratio); // Left Arm - 2
                } else {
                    imagecopy($dest, $src, 12 * $ratio, 8 * $ratio, 36 * $ratio, 52 * $ratio, 4 * $ratio, 12 * $ratio); // Left Arm - 1
                    imagecopy($dest, $src, 0 * $ratio, 8 * $ratio, 44 * $ratio, 36 * $ratio, 4 * $ratio, 12 * $ratio); // Right Arm - 2
                    imagecopy($dest, $src, 12 * $ratio, 8 * $ratio, 52 * $ratio, 52 * $ratio, 4 * $ratio, 12 * $ratio); // Left Arm - 2
                }
            } else {
                // I am not sure whether there are single layer Alex-model skin.
                if ($alex) {
                    static::imageflip($dest, $src, 12 * $ratio, 8 * $ratio, 44 * $ratio, 20 * $ratio, 3 * $ratio, 12 * $ratio); // Left Arm
                } else {
                    static::imageflip($dest, $src, 12 * $ratio, 8 * $ratio, 44 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio); // Left Arm
                }
                static::imageflip($dest, $src, 8 * $ratio, 20 * $ratio, 4 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio); // Left Leg
            }
        }

        if ($side == 'both' || $side == 'back') {
            imagecopy($dest, $src, $half_width + 4 * $ratio, 8 * $ratio, 32 * $ratio, 20 * $ratio, 8 * $ratio, 12 * $ratio); // Body
            imagecopy($dest, $src, $half_width + 4 * $ratio, 0 * $ratio, 24 * $ratio, 8 * $ratio, 8 * $ratio, 8 * $ratio); // Head
            imagecopy($dest, $src, $half_width + 8 * $ratio, 20 * $ratio, 12 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio); // Right Leg
            imagecopy($dest, $src, $half_width + 4 * $ratio, 0 * $ratio, 56 * $ratio, 8 * $ratio, 8 * $ratio, 8 * $ratio); // Headwear

            if ($alex) {
                imagecopy($dest, $src, $half_width + 12 * $ratio, 8 * $ratio, 51 * $ratio, 20 * $ratio, 3 * $ratio, 12 * $ratio); // Right Arm
            } else {
                imagecopy($dest, $src, $half_width + 12 * $ratio, 8 * $ratio, 52 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio); // Right Arm
            }

            if ($double) {
                if ($alex) {
                    imagecopy($dest, $src, $half_width + 1 * $ratio, 8 * $ratio, 43 * $ratio, 52 * $ratio, 3 * $ratio, 12 * $ratio);
                } else {
                    imagecopy($dest, $src, $half_width + 0 * $ratio, 8 * $ratio, 44 * $ratio, 52 * $ratio, 4 * $ratio, 12 * $ratio);
                }
                imagecopy($dest, $src, $half_width + 4 * $ratio, 20 * $ratio, 28 * $ratio, 52 * $ratio, 4 * $ratio, 12 * $ratio); // Left Leg

                // copy second layer
                imagecopy($dest, $src, $half_width + 4 * $ratio, 8 * $ratio, 32 * $ratio, 36 * $ratio, 8 * $ratio, 12 * $ratio);
                imagecopy($dest, $src, $half_width + 12 * $ratio, 8 * $ratio, 52 * $ratio, 36 * $ratio, 4 * $ratio, 12 * $ratio);
                if ($alex) {
                    imagecopy($dest, $src, $half_width + 1 * $ratio, 8 * $ratio, 59 * $ratio, 52 * $ratio, 3 * $ratio, 12 * $ratio);
                } else {
                    imagecopy($dest, $src, $half_width + 0 * $ratio, 8 * $ratio, 60 * $ratio, 52 * $ratio, 4 * $ratio, 12 * $ratio);
                }
                imagecopy($dest, $src, $half_width + 8 * $ratio, 20 * $ratio, 12 * $ratio, 36 * $ratio, 4 * $ratio, 12 * $ratio);
                imagecopy($dest, $src, $half_width + 4 * $ratio, 20 * $ratio, 12 * $ratio, 52 * $ratio, 4 * $ratio, 12 * $ratio);
            } else {
                static::imageflip($dest, $src, $half_width + 0 * $ratio, 8 * $ratio, 52 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio);
                static::imageflip($dest, $src, $half_width + 4 * $ratio, 20 * $ratio, 12 * $ratio, 20 * $ratio, 4 * $ratio, 12 * $ratio);
            }
        }

        $width = ($side == 'both') ? $height / 32 * (32 + $gap) : $height / 2;

        $fullsize = imagecreatetruecolor($width, $height);
        imagesavealpha($fullsize, true);
        $transparent = imagecolorallocatealpha($fullsize, 255, 255, 255, 127);
        imagefill($fullsize, 0, 0, $transparent);
        imagecopyresized($fullsize, $dest, 0, 0, 0, 0, imagesx($fullsize), imagesy($fullsize), imagesx($dest), imagesy($dest));

        imagedestroy($dest);
        imagedestroy($src);

        return $fullsize;
    }

    /**
     * Generate a image preview for a cape texture.
     *
     * @param string $binary     binary image data or decoded base64 formatted image
     * @param int    $height     the size of generated image in pixel
     * @param int    $fillWidth  create a image with given size, And draw the preview on the center of it
     * @param int    $fillHeight set the value to 0 to disable
     *
     * @return resource
     */
    public static function generatePreviewFromCape(string $binary, int $height, $fillWidth = 0, $fillHeight = 0)
    {
        $src = imagecreatefromstring($binary);
        $ratio = imagesx($src) / 64;
        $width = $height / 16 * 10;

        $dest = imagecreatetruecolor($width, $height);
        imagesavealpha($dest, true);
        $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
        imagefill($dest, 0, 0, $transparent);
        imagecopyresized($dest, $src, 0, 0, $ratio, $ratio, $width, $height, imagesx($src) * 10 / 64, imagesy($src) * 16 / 32);

        imagedestroy($src);
        if ($fillWidth == 0 || $fillHeight == 0) {
            return $dest;
        }

        $filled = imagecreatetruecolor($fillWidth, $fillHeight);
        imagesavealpha($filled, true);
        $transparent = imagecolorallocatealpha($filled, 255, 255, 255, 127);
        imagefill($filled, 0, 0, $transparent);
        imagecopyresized($filled, $dest, ($fillWidth - $width) / 2, ($fillHeight - $height) / 2, 0, 0, $width, $height, $width, $height);

        imagedestroy($dest);

        return $filled;
    }

    /**
     * Flip the given image.
     */
    protected static function imageflip(&$result, &$img, $rx = 0, $ry = 0, $x = 0, $y = 0, $size_x = null, $size_y = null)
    {
        $size_x = ($size_x < 1) ? $imagesx($img) : $size_x;
        $size_y = ($size_y < 1) ? $imagesy($img) : $size_y;

        imagecopyresampled($result, $img, $rx, $ry, ($x + $size_x - 1), $y, $size_x, $size_y, 0 - $size_x, $size_y);
    }
}
