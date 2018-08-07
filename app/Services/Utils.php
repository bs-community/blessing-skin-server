<?php

namespace App\Services;

use Log;

class Utils
{
    /**
     * Returns the client IP address.
     *
     * This method is defined because Symfony's Request::getClientIp() needs "setTrustedProxies()"
     * which sucks when load balancer is enabled.
     *
     * @deprecated Use the helper function instead.
     * @return string
     */
    public static function getClientIp()
    {
        return get_client_ip();
    }

    /**
     * Checks whether the request is secure or not.
     * True is always returned when "X-Forwarded-Proto" header is set.
     *
     * This method is defined because Symfony's Request::isSecure() needs "setTrustedProxies()"
     * which sucks when load balancer is enabled.
     *
     * @deprecated Use the helper function instead.
     * @return bool
     */
    public static function isRequestSecure()
    {
        return is_request_secure();
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

    /**
     * Get date time string in "Y-m-d H:i:s" format.
     *
     * @deprecated Use the helper function instead.
     * @param integer $timestamp
     * @return string
     */
    public static function getTimeFormatted($timestamp = 0)
    {
        return get_datetime_string($timestamp);
    }

    /**
     * Replace content of string according to given rules.
     *
     * @deprecated Use the helper function instead.
     * @param  string $str
     * @param  array  $rules
     * @return string
     */
    public static function getStringReplaced($str, $rules)
    {
        return get_string_replaced($str, $rules);
    }

}
