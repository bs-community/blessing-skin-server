<?php

namespace App\Services;

use Log;
use Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Exceptions\PrettyPageException;

class Utils
{
    /**
     * Returns the client IP address.
     *
     * This method is defined because Symfony's Request::getClientIp() needs "setTrustedProxies()"
     * which sucks when load balancer is enabled.
     *
     * @return string
     */
    public static function getClientIp()
    {
        if (option('ip_get_method') == "0") {
            // Fallback to REMOTE_ADDR
            $ip = array_get(
                $_SERVER, 'HTTP_X_FORWARDED_FOR',
                array_get($_SERVER, 'HTTP_CLIENT_IP', $_SERVER['REMOTE_ADDR'])
            );
        } else {
            $ip = array_get($_SERVER, 'REMOTE_ADDR');
        }

        return $ip;
    }

    /**
     * Checks whether the request is secure or not.
     * True is always returned when "X-Forwarded-Proto" header is set.
     *
     * This method is defined because Symfony's Request::isSecure() needs "setTrustedProxies()"
     * which sucks when load balancer is enabled.
     *
     * @return bool
     */
    public static function isRequestSecure()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
            return true;

        if (! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            return true;

        if (! empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
            return true;

        return false;
    }

    public static function download($url, $path)
    {
        @set_time_limit(0);

        touch($path);

        Log::info("[File Downloader] Download started, source: $url");
        Log::info("[File Downloader] ======================================");

        $context = stream_context_create(['http' => [
            'method' => 'GET',
            'header' => 'User-Agent: '.menv('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36')
        ]]);

        if ($fp = fopen($url, 'rb', false, $context)) {

            if (! $download_fp = fopen($path, 'wb')) {
                return false;
            }

            while (! feof($fp)) {

                if (! file_exists($path)) {
                    // Cancel downloading if destination is no longer available
                    fclose($download_fp);

                    return false;
                }

                Log::info('[File Downloader] 1024 bytes wrote');
                fwrite($download_fp, fread($fp, 1024 * 8 ), 1024 * 8);
            }

            fclose($download_fp);
            fclose($fp);

            Log::info("[File Downloader] Finished downloading, data stored to: $path");
            Log::info("[File Downloader] ===========================================");

            return true;
        } else {
            return false;
        }
    }

    public static function getRemoteFileSize($url)
    {
        $regex = '/^Content-Length: *+\K\d++$/im';

        if (! $fp = @fopen($url, 'rb')) {
            return false;
        }

        if (
            isset($http_response_header) &&
            preg_match($regex, implode("\n", $http_response_header), $matches)
        ) {
            return (int)$matches[0];
        }

        return strlen(stream_get_contents($fp));
    }

    public static function getTimeFormatted($timestamp = 0)
    {
        return ($timestamp == 0) ? Carbon::now()->toDateTimeString() : Carbon::createFromTimestamp($timestamp)->toDateTimeString();
    }

    /**
     * Replace content of string according to given rules.
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

    /**
     * Convert error number of uploading files to human-readable text.
     *
     * @param  int  $errno
     * @return string
     */
    public static function convertUploadFileError($errno = 0)
    {
        $phpFileUploadErrors = [
            0 => 'There is no error, the file uploaded with success',
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.',
        ];

        return $phpFileUploadErrors[$errno];
    }

}
