<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 21:44:11
 */

class Utils
{
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
        if (file_exists($filename)) {
            return unlink($filename);
        }
    }

    public static function removeDir($dir) {
        $resource = opendir($dir);
        $size = 0;
        while($filename = @readdir($resource)) {
            if ($filename != "." && $filename != "..") {
                $path = $dir.$filename;
                if (is_dir($path)) {
                    // recursion
                    self::removeDir($path."/");
                } else {
                    unlink($path);
                }
            }
        }
        closedir($resource);

        return rmdir($dir);
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
        while($filename = @readdir($resource)) {
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
     * Copy directory recursively
     *
     * @param  string $source
     * @param  string $dest
     * @return bool
     */
    public static function copyDir($source, $dest) {
        if(!is_dir($source)) return false;
        if(!is_dir($dest)) mkdir($dest, 0777, true);

        $handle = dir($source);

        while($entry = $handle->read()) {
            if ($entry != "." && $entry != "..") {
                if (is_dir($source.'/'.$entry)) {
                    // recursion
                    self::copyDir($source.'/'.$entry, $dest.'/'.$entry);
                } else {
                    @copy($source.'/'.$entry, $dest.'/'.$entry);
                    // echo $source.'/'.$entry." => ".$dest.'/'.$entry."<br />";
                }
            }
        }
        return true;
    }

    /**
     * Simple SQL injection protection
     *
     * @param  string $string
     * @return string
     */
    public static function convertString($string) {
        return addslashes(trim($string));
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
     * Cut and resize to get avatar from skin, HD support by <xfl03@hotmail.com>
     *
     * @author https://github.com/jamiebicknell/Minecraft-Avatar/blob/master/face.php
     * @param  string $hash
     * @param  int    $size
     * @param  string $view, default for 'f'
     * @return resource
     */
    public static function generateAvatarFromSkin($hash, $size, $view='f') {
        $src = imagecreatefrompng(BASE_DIR."/textures/$hash");
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
     * Check if given texture is occupied
     *
     * @param  string $hash
     * @return bool
     */
    public static function checkTextureOccupied($hash) {
        $db = new Database\Database('users');
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

    /**
     * Generate random string
     *
     * @param  int $length
     * @return string
     */
    public static function generateRndString($length) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        $rnd_string = '';
        for ($i = 0; $i < $length; $i++) {
            $rnd_string .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $rnd_string;
    }

    /**
     * HTTP redirect
     *
     * @param  string $url
     * @return null
     */
    public static function redirect($url, $use_js = false) {
        if ($use_js)
            echo "<script>window.location = '$url';</script>";
        else
            header('Location: '.$url);
    }

}
