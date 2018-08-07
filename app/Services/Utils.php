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
