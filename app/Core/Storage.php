<?php

namespace Blessing;

class Storage
{
    /**
     * Read a file and return bin data
     *
     * @param  string $filename
     * @return string|bool
     */
    public static function get($filename)
    {
        $result = file_get_contents($filename, 'r');
        if (false === $result) {
            throw new \Exception("Failed to read $filename.");
        }
        return $result;
    }

    public static function exists($filename)
    {
        return file_exists($filename);
    }

    public static function hash($filename, $type = 'sha256')
    {
        return hash_file('sha256', $filename);
    }

    public static function rename($fname, $new_fname)
    {
        if (false === rename($fname, $new_fname)) {
            throw new \Exception("Failed to rename $fname to $new_fname.");
        }
        return $new_fname;
    }

    public static function size($filename)
    {
        if (self::exists($filename)) {
            return filesize($filename);
        } else {
            return 0;
        }
    }

    /**
     * Remove a file
     *
     * @param  $filename
     * @return $bool
     */
    public static function remove($filename)
    {
        if (self::exists($filename)) {
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
        if(!is_dir($source))
            return false;
        if(!is_dir($dest))
            mkdir($dest, 0777, true);

        $handle = dir($source);

        while($entry = $handle->read()) {
            if ($entry != "." && $entry != "..") {
                if (is_dir($source.'/'.$entry)) {
                    // recursion
                    self::copyDir($source.'/'.$entry, $dest.'/'.$entry);
                } else {
                    @copy($source.'/'.$entry, $dest.'/'.$entry);
                }
            }
        }
        return true;
    }
}
