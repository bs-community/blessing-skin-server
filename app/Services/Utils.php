<?php

namespace App\Services;

use Log;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Storage as LaravelStorage;
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
            // fallback to REMOTE_ADDR
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

        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            return true;

        if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
            return true;

        return false;
    }

    /**
     * Compares two "PHP-standardized" version number strings.
     * Unlike version_compare(), this method will determine that versions with suffix are lower.
     *
     * e.g. 3.2-beta > 3.2-alpha
     *      3.2 > 3.2-beta
     *      3.2 > 3.2-pre-release
     *      3.2 > 3.2-pr8
     *
     * @param  string $version1
     * @param  string $version2
     * @param  string $operator
     * @return mixed
     */
    public static function versionCompare($version1, $version2, $operator = null)
    {
        $versions = [$version1, $version2];

        // pre-processing for version contains hyphen
        foreach ([0, 1] as $offset) {
            if (false !== ($result = self::parseVersionWithHyphen($versions[$offset]))) {
                $versions[$offset] = $result;
            } else {
                $versions[$offset] = ['main' => $versions[$offset], 'sub' => ''];
            }
        }

        if (version_compare($versions[0]['main'], $versions[1]['main'], '=')) {
            $sub1 = $versions[0]['sub'];
            $sub2 = $versions[1]['sub'];

            // v3.2-pr < v3.2
            if ($sub1 != "" || $sub2 != "") {
                // if both of sub-versions are not empty
                if ($sub1 != "" && $sub2 != "") {
                    return version_compare($sub1, $sub2, $operator);
                } else {
                    $result = version_compare($sub1, $sub2, $operator);
                    // reverse the result since version_compare() will determine that "beta" > ""
                    return ($operator == "=") ? $result : !$result;
                }
            }
        }

        return version_compare($versions[0]['main'], $versions[1]['main'], $operator);
    }

    public static function parseVersionWithHyphen($version)
    {
        preg_match('/([^-]*)-(.*)/', $version, $matches);

        if (isset($matches[2])) {
            return [
                'main' => $matches[1],
                'sub'  => $matches[2]
            ];
        }

        return false;
    }

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

                if (!LaravelStorage::disk('textures')->has($hash)) {
                    LaravelStorage::disk('textures')->move($path, $hash);
                } else {
                    // delete the temp file
                    unlink($absolute_path);
                }

                return $hash;
            } else {
                Log::warning("Failed to upload file $path");
            }
        }
    }

    public static function download($url, $path)
    {
        @set_time_limit(0);

        touch($path);

        Log::info("[File Downloader] Download started, source: $url");
        Log::info("[File Downloader] ======================================");

        if ($fp = fopen($url, "rb")) {

            if (!$download_fp = fopen($path, "wb")) {
                return false;
            }

            while (!feof($fp)) {

                if (!file_exists($path)) {
                    // cancel downloading if destination is no longer available
                    fclose($download_fp);

                    return false;
                }

                Log::info("[Download] 1024 bytes wrote");
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

        if (!$fp = @fopen($url, 'rb')) {
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
