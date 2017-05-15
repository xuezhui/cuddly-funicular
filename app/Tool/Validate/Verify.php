<?php
/**
 * Author: yanpengcheng
 * DateTime: 2017/3/23 12:20
 * Description:
 *
 */

namespace App\Tool\Validate;


class Verify
{
    /**
     * @descrition:手机号码段规则
     * 13段：130、131、132、133、134、135、136、137、138、139
     * 14段：145、147
     * 15段：150、151、152、153、155、156、157、158、159
     * 17段：170、176、177、178
     * 18段：180、181、182、183、184、185、186、187、188、189
     *
     */
    public static function isPhone($phone)
    {
        $pattern =  '/^(13[0-9]|14[57]|15[012356789]|17[0678]|18[0-9])\d{8}$/';
        return preg_match($pattern, $phone);
    }

    /**
     * 校验是否是日期时间
     * @param $date
     * @return int
     */
    public static function isDateTime($date)
    {
        $pattern = '/^\d{4}[-](0?[1-9]|1[012])[-](0?[1-9]|[12][0-9]|3[01])(s+(0?[0-9]|[12][0-3]):(0?[0-9]|[1-5][1-9]):(0?[0-9]|[1-5][1-9]))?$/';
        return preg_match($pattern, $date);
    }
}