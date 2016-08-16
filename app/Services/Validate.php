<?php

namespace App\Services;

use App\Exceptions\E;

class Validate
{
    /**
     * Check POST values in a simple way
     *
     * @param  array  $keys
     * @return void
     */
    public static function checkPost(Array $keys, $silent = false)
    {
        foreach ($keys as $key) {
            if (!isset($_POST[$key])) {
                if ($silent) return false;
                throw new E('非法参数', 1);
            }
        }
        return true;
    }

    public static function email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function nickname($nickname)
    {
        return $nickname != Utils::convertString($nickname);
    }

    public static function playerName($player_name)
    {
        $regx = (Option::get('allow_chinese_playername') == "1") ?
                "/^([A-Za-z0-9\x{4e00}-\x{9fa5}_]+)$/u" : "/^([A-Za-z0-9_]+)$/";
        return preg_match($regx, $player_name);
    }

    public static function textureName($texture_name)
    {
        if (strlen($texture_name) > 32 || strlen($texture_name) < 1) {
            throw new E('无效的材质名称。材质名长度应该小于 32。', 2);
        } else if (Utils::convertString($texture_name) != $texture_name) {
            throw new E('无效的材质名称。材质名称中包含了奇怪的字符。', 2);
        }
        return true;
    }

    public static function password($password, $silent = false)
    {
        if (strlen($password) > 16 || strlen($password) < 8) {
            if ($silent) return false;
            throw new E('无效的密码。密码长度应该大于 8 并小于 16。', 2);
        } else if (Utils::convertString($password) != $password) {
            if ($silent) return false;
            throw new E('无效的密码。密码中包含了奇怪的字符。', 2);
        }
        return true;
    }
}
