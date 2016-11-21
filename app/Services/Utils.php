<?php

namespace App\Services;

use Log;
use Storage;
use App\Exceptions\PrettyPageException;

class Utils
{
    /**
     * Rename uploaded file
     *
     * @param  array  $file files uploaded via HTTP POST
     * @return string $hash sha256 hash of file
     */
    public static function upload($file)
    {
        $path = 'tmp'.time();
        $absolute_path = storage_path("textures/$path");

        try {
            if (false === move_uploaded_file($file['tmp_name'], $absolute_path)) {
                throw new \Exception('Failed to remove uploaded files, please check the permission', 1);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to move uploaded file, $e");
        } finally {
            if (file_exists($absolute_path)) {
                $hash = hash_file('sha256', $absolute_path);

                if (!Storage::disk('textures')->has($hash)) {
                    Storage::disk('textures')->move($path, $hash);
                }

                return $hash;
            } else {
                Log::warning("Failed to upload file $path");
            }
        }
    }

    /**
     * Generate random string
     *
     * @param  int  $length
     * @param  bool $special_chars
     * @return string
     */
    public static function generateRndString($length, $special_chars = true)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        if ($special_chars) $chars .= "!@#$%^&*()-_ []{}<>~`+=,.;:/?|";

        $rnd_string = '';
        for ($i = 0; $i < $length; $i++) {
            $rnd_string .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $rnd_string;
    }

    public static function getTimeFormatted($timestamp = 0)
    {
        return ($timestamp == 0) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Replace content of string according to given rules
     *
     * @param  string $str
     * @param  array  $rules
     * @return string
     */
    public static function getStringReplaced($str, $rules)
    {
        foreach ($rules as $search => $replace) {
            $str = str_replace($search, $replace, $str);
        }
        return $str;
    }

    public static function checkTextureDirectory()
    {
        if (!Storage::disk('storage')->has('textures')) {
            // mkdir
            if (!Storage::disk('storage')->makeDirectory('textures'))
                throw new PrettyPageException(trans('setup.file.permission-error'), -1);
        }
    }

}
