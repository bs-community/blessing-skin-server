<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-26 21:35:53
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
     * @author https://github.com/jamiebicknell/Minecraft-Avatar/blob/master/face.php
     * @param  string $hash
     * @param  int    $size
     * @return resource
     */
    public static function generateAvatarFromSkin($hash, $size, $view='f') {
        $src = imagecreatefrompng(BASE_DIR."/textures/$hash");
        $dest = imagecreatetruecolor($size, $size);

        // f => front, l => left, r => right, b => back
        $x = array('f' => 8, 'l' => 16, 'r' => 0, 'b' => 24);

        imagecopyresized($dest, $src, 0, 0, $x[$view], 8, $size, $size, 8, 8);         // Face
        imagecolortransparent($src, imagecolorat($src, 63, 0));                       // Black Hat Issue
        imagecopyresized($dest, $src, 0, 0, $x[$view] + 32, 8, $size, $size, 8, 8);    // Accessories

        imagedestroy($src);
        return $dest;
    }

    /**
     * Check if given texture is occupied
     *
     * @param  string $hash
     * @return bool
     */
    public static function checkTextureOccupied($hash) {
        $db = new Database\Database();
        if ($db->getNumRows('hash_steve', $hash) > 1) {
            return true;
        } elseif ($db->getNumRows('hash_alex', $hash) > 1) {
            return true;
        } elseif ($db->getNumRows('hash_cape', $hash) > 1) {
            return true;
        }
        // finally if given texture is not used by anyone else
        return false;
    }

}
