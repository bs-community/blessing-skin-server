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
     * @param  array  $array
     * @return string|boolean
     */
    public static function getValue($key, $array) {
        return array_key_exists($key, $array) ? $array[$key] : false;
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
        return ($timestamp == 0) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $timestamp);
    }

    public static function getNameOrEmail(\App\Models\User $user)
    {
        return ($user->getNickName() == '') ? $_SESSION['email'] : $user->getNickName();
    }

    public static function getAvatarFname(\App\Models\User $user)
    {
        $fname = base64_encode($user->email).".png";
        if (Option::get('avatar_query_string')) {
            $fname .= '?v='.$user->getAvatarId();
        }
        return $fname;
    }

    /**
     * Generate omitted string
     *
     * @param  string  $str
     * @param  int     $length
     * @param  boolean $append
     * @return string
     */
    public static function getStringOmitted($str, $length, $append = true)
    {
        $str       = trim($str);
        $strlength = strlen($str);

        if ($length == 0 || $length >= $strlength) {
            return $str;
        } elseif ($length < 0) {
            $length = $strlength + $length;

            if ($length < 0) {
                $length = $strlength;
            }
        }

        if (function_exists('mb_substr')) {
            $newstr = mb_substr($str, 0, $length, 'utf-8');
        } elseif (function_exists('iconv_substr')) {
            $newstr = iconv_substr($str, 0, $length, 'utf-8');
        } else {
            $newstr = substr($str, 0, $length);
        }

        if ($append && $str != $newstr) {
            $newstr .= '...';
        }

        return $newstr;
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

}
