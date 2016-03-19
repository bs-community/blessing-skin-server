<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-19 19:54:09
 */

class Utils
{
    /**
     * Custom error handler
     *
     * @param  int $errno
     * @param  string $msg, message to show
     * @return void
     */
    public static function raise($errno = -1, $msg = "Error occured.") {
        $exception['errno'] = $errno;
        $exception['msg'] = $msg;
        header('Content-type: application/json; charset=utf-8');
        die(json_encode($exception));
    }

    public static function showErrorPage($errno = -1, $msg = "Error occured.") {
        require BASE_DIR."/templates/error.tpl.php";
        die();
    }

    /**
     * Rename uploaded file
     *
     * @param  array $file, files uploaded via HTTP POST
     * @return string $hash, sha256 hash of file
     */
    public static function upload($file) {
        move_uploaded_file($file["tmp_name"], BASE_DIR."/textures/tmp.png");
        $hash = hash_file('sha256', BASE_DIR."/textures/tmp.png");
        rename(BASE_DIR."/textures/tmp.png", BASE_DIR."/textures/".$hash);
        return $hash;
    }

    /**
     * Read a file and return bin data
     *
     * @param  string $filename
     * @return resource, binary data
     */
    public static function fread($filename) {
        return fread(fopen($filename, 'r'), filesize($filename));
    }

    /**
     * Remove a file
     *
     * @param  $filename
     * @return $bool
     */
    public static function remove($filename) {
        if(file_exists($filename)) {
            if (!unlink($filename)) {
                self::raise(-1, "删除 $filename 的时候出现了奇怪的问题。。请联系作者");
            } else {
                return true;
            }
        }
    }

    /**
     * Recursively count the size of specified directory
     *
     * @param  string $dir
     * @return int, total size in bytes
     */
    public static function getDirSize($dir) {
        $resource = opendir($dir);
        $size = 0;
        while($filename = readdir($resource)) {
            if ($filename != "." && $filename != "..") {
                $path = $dir.$filename;
                if (is_dir($path)) {
                    // recursion
                    $size += self::getDirSize($path);
                } else if (is_file($path)) {
                    $size += filesize($path);
                }
            }
        }
        closedir($resource);
        return $size;
    }

    /**
     * Recursively count files of specified directory
     *
     * @param  string $dir
     * @param  $file_num
     * @return int, total size in bytes
     */
    public static function getFileNum($dir, $file_num = 0) {
        $resource = opendir($dir);
        while($filename = readdir($resource)) {
            if ($filename != "." && $filename != "..") {
                $path = $dir.$filename;
                if (is_dir($path)) {
                    // recursion
                    $file_num = self::getFileNum($path, $file_num);
                } else {
                    $file_num++;
                }
            }
        }
        closedir($resource);
        return $file_num;
    }

    /**
     * Simple SQL injection protection
     *
     * @param  string $string
     * @return string
     */
    public static function convertString($string) {
        return stripslashes(trim($string));
    }

    /**
     * Get the value of key in an array if index exist
     *
     * @param  string $key
     * @param  array $array
     * @return object
     */
    public static function getValue($key, $array) {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        return false;
    }

    /**
     * Cut and resize to get avatar from skin
     *
     * @param  string $username
     * @param  int     $size
     * @return null, will output directly
     */
    public static function getAvatarFromSkin($username, $size) {
        $user = new User($username);
        $model_preferrnce = ($user->getPreference() == "default") ? "steve" : "alex";
        if ($user->getTexture($model_preferrnce) != "") {
            $src = imagecreatefrompng(BASE_DIR."/textures/".$user->getTexture($model_preferrnce));

            $dest = imagecreatetruecolor(8, 8);
            $final = imagecreatetruecolor($size, $size);
            imagecopy($dest, $src, 0, 0, 8, 8, 8, 8);

            // Get image width and height
            $w = imagesx($src);
            $h = imagesy($src);
            // Turn alpha blending off
            imagealphablending($src, false);
            // Find the most opaque pixel in the image (the one with the smallest alpha value)
            $minalpha = 127;
            for($x = 0; $x < $w; $x++)
            for($y = 0; $y < $h; $y++) {
                $alpha = (imagecolorat($src, $x, $y) >> 24) & 0xFF;
                if ($alpha < $minalpha) {
                    $minalpha = $alpha;
                }
            }
            // loop through image pixels and modify alpha for each
            for($x = 0; $x < $w; $x++) {
                for($y = 0; $y < $h; $y++) {
                    // get current alpha value (represents the TANSPARENCY!)
                    $colorxy = imagecolorat($src, $x, $y);
                    $alpha = ($colorxy >> 24) & 0xFF;
                    // calculate new alpha
                    if ($minalpha !== 127) {
                        $alpha = 127 + 127 * 1 * ($alpha - 127) / (127 - $minalpha);
                    } else {
                        $alpha += 127 * 1;
                    }
                    // get the color index with new alpha
                    $alphacolorxy = imagecolorallocatealpha($src, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
                    // set pixel with the new color + opacity
                    imagesetpixel($src, $x, $y, $alphacolorxy);
                }
            }
            // The image copy
            imagecopy($dest, $src, 0, 0, 56, 8, 8, 8);
            imagecopyresized($final, $dest, 0, 0, 0, 0, $size, $size, 8, 8);

            header('Content-Type: image/png');
            imagepng($final);

            imagedestroy($dest);
            imagedestroy($final);
            imagedestroy($src);
        } else {
            header('Content-Type: image/png');
            echo Utils::fread(BASE_DIR."/assets/images/steve-avatar.png");
        }

    }

}
