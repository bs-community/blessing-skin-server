<?php

namespace App\Services;

class Storage
{
    /**
     * Rename uploaded file
     *
     * @param  array $file, files uploaded via HTTP POST
     * @return string $hash, sha256 hash of file
     */
    public static function upload($file)
    {
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
    public static function fread($filename)
    {
        return fread(fopen($filename, 'r'), filesize($filename));
    }

    public static function exist($filename)
    {
        return file_exists($filename);
    }

    /**
     * Remove a file
     *
     * @param  $filename
     * @return $bool
     */
    public static function remove($filename)
    {
        if (file_exists($filename)) {
            return unlink($filename);
        }
    }

    public static function removeDir($dir)
    {
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
    public static function getDirSize($dir)
    {
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
    public static function getFileNum($dir, $file_num = 0)
    {
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
    public static function copyDir($source, $dest)
    {
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
}
