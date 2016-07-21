<?php

namespace App\Services;

use App\Exceptions\E;

class Utils
{
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

    public static function getTimeFormatted($timestamp = 0)
    {
        // set default time zone to UTC+8
        date_default_timezone_set('Asia/Shanghai');
        return ($timestamp == 0) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $timestamp);
    }

    public static function checkPost(Array $keys)
    {
        foreach ($keys as $key) {
            if (!isset($_POST[$key]))
                throw new E('Invalid parameters.', 1);
        }
    }

}
