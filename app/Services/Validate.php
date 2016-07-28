<?php

namespace App\Services;

use App\Exceptions\E;

class Validate
{
    public static function checkValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function checkValidPlayerName($player_name)
    {
        $regx = (Option::get('allow_chinese_playername') == "1") ?
                "/^([A-Za-z0-9\x{4e00}-\x{9fa5}_]+)$/u" : "/^([A-Za-z0-9_]+)$/";
        return preg_match($regx, $player_name);
    }

    public static function checkValidTextureName($texture_name)
    {
        if (strlen($texture_name) > 32 || strlen($texture_name) < 1) {
            throw new E('无效的材质名称。材质名长度应该小于 32。', 2);
        } else if (Utils::convertString($texture_name) != $texture_name) {
            throw new E('无效的材质名称。材质名称中包含了奇怪的字符。', 2);
        }
        return true;
    }

    public static function checkValidPwd($passwd)
    {
        if (strlen($passwd) > 16 || strlen($passwd) < 8) {
            throw new E('无效的密码。密码长度应该大于 8 并小于 16。', 2);
        } else if (Utils::convertString($passwd) != $passwd) {
            throw new E('无效的密码。密码中包含了奇怪的字符。', 2);
        }
        return true;
    }
}
