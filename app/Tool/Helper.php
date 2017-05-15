<?php
/**
 * Author: yanpengcheng
 * DateTime: 2017/3/23 19:32
 * Description:
 *
 */


namespace App\Tool;


class Helper
{
    public static function formatMoney($amount)
    {
        //舍去法保留两位小数
        return (float)sprintf("%.2f",substr(sprintf("%.3f", $amount), 0, -1));
    }
}