<?php

namespace App\Tool\Validate;

/**
 * @description 加解密字符串类
 * @param $string 需要加解密的字符串
 * @param $operation: D表示解密 E表示加密
 * @param $key 秘钥
 * @example $str = 'abc';
    $key = 'www.helloweba.com';
    $token = letSecret($str, 'E', $key);
    echo '加密:'.letSecret($str, 'E', $key);
    echo '解密：'.letSecret($str, 'D', $key);
 */
class Aes
{
    public static function letSecret($string, $operation, $key = '')
    {

    }
}
