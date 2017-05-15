<?php
/**
 * Author: yanpengcheng
 * DateTime: 2017/3/23 12:20
 * Description:
 *
 */

namespace App\Tool;

class ConstantCode
{
    /*==========================资金流水日志表capitallog==========================*/

    //用户充值
    const CAPITAL_TYPE_RECHARGE = 1;
    //用户消费
    const CAPITAL_TYPE_CONSUMPTION = 2;
    //分销返利
    const CAPITAL_TYPE_REBATE = 3;
    //用户提现
    const CAPITAL_TYPE_WITHDRAWALS = 4;
    //基金每天减少
    const CAPITAL_TYPE_FUND_DECREASE = 5;
    //提现每天增加
    const CAPITAL_TYPE_WITHDRAWALS_INCREASE = 6;

    /*==========================用户表user==========================*/
    //不是代理
    const NOT_AGENT = 0;
    //是代理
    const IS_AGENT = 1;
    //默认代理级别
    const AGENT_LEVEL_DEFAULT = 0;
    //省级代理
    const AGENT_LEVEL_PROVINCE = 3;
    //市级代理
    const AGENT_LEVEL_CITY = 2;
    //县级代理
    const AGENT_LEVEL_COUNTY = 1;
    //不是会员
    const NOT_MEMBER = 0;
    //是会员
    const IS_MEMBER = 1;
}