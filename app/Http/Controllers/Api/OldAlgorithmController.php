<?php

namespace App\Http\Controllers\Api;

use App\Models\ErrorCode;
use App\Models\M3Result;
use App\Tool\Helper;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OldAlgorithmController extends Controller
{
    /**
     * 此算法暂时废弃
     * 统计代理下面所有人的充值量 并计算出每级代理的分成
     * 每条代理线所有代理的分成率总和为5%
     * 1. 省-1% 市-1% 县-3%
     * 2. 省-1% 市-4%
     * 3. 省-2% 县-3%
     * 4. 市-1% 县-3%
     * 5. 省-5%
     * 6. 市-4%
     * 7. 县-3%
     * 每当用户充值的时候计算该用户的代理的所得
     * 代理返利直接返现金
     */
    public function calculateAgentIncome($user_id, $amount)
    {
        $user = DB::table('user')->where('id', $user_id)->first();
        if (is_null($user)) {
            Log::info('用户'.$user_id.'代理返利流程未走,可能原因用户session失效');
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }
        $related_str = $user->related_str;
        $related_str_arr = explode('-', $related_str);
        if (is_array($related_str_arr) && count($related_str_arr)) {
            //反转后的关系数组 下级往上级一级一级找
            $related_str_arr = array_reverse($related_str_arr);
            //表征各个代理是否被找到
            $county_found = false;
            $city_found = false;
            $province_found = false;
            //存储关系字符串的三级代理被找到的次数
            $county_count = 0;
            $city_count = 0;
            //用来存储关系字符串最先找到的三级代理的各个代理人ID
            $county_id = 0;
            $city_id = 0;
            $province_id = 0;
            //$related_str_arr里存的是所有上级的用户id 顺序是从下往上
            foreach ($related_str_arr as $item) {
                $tmp_user = DB::table('user')->where('id', $item)->first();
                //如果不是代理就跳出循环
                if ($tmp_user->is_agent == 0) {
                    continue;
                }
                //代理查找规则是先找县级 再找市级 再找省级
                if ($tmp_user->agent_level == 1) {
                    if ($county_count >= 1) {
                        continue;
                    }
                    $county_found = true;//县级代理第一次找到
                    $county_count++;
                    $county_id = $tmp_user->id;
                    contiue;
                }

                if ($tmp_user->agent_level == 2) {
                    if ($city_count >= 1) {
                        continue;
                    }
                    $city_found = true;//市级代理第一次找到
                    $city_id = $tmp_user->id;
                    $city_count++;
                    contiue;
                }

                if ($tmp_user->agent_level == 3) {
                    $province_found = true;
                    $province_id = $tmp_user->id;
                    break;//省级代理找到 不再查找结束循环
                }

            }
            //根据找到的参数决定回报率
            $rates = $this->determinAgentRate([
                'county_found' => $county_found,
                'city_found' => $city_found,
                'province_found' => $province_found,
            ]);
            if ($rates['province_rate']) {
                $this->setAgentProfit($user, $amount, $rates['province_rate'], $province_id);
            }

            if ($rates['city_rate']) {
                $this->setAgentProfit($user, $amount, $rates['city_rate'], $city_id);
            }

            if ($rates['county_rate']) {
                $this->setAgentProfit($user, $amount, $rates['county_rate'], $county_id);
            }
        }
        Log::error('用户'.$user->id.'关系字符串解析异常');
    }

    /**
     * @param $user
     * @param $amount
     * @param $rate
     * @param $agent_id
     */
    private function setAgentProfit($user, $amount, $rate, $agent_id)
    {
        //如果没有记录需要先插入一条记录
        $agent = DB::table('fund_pool')->where('member_id', $agent_id)->first();
        if (is_null($agent)) {
            $this->setAgentFirstRecord($user, $amount, $rate, $agent_id);
        } else {
            $this->incrementAgentProfit($user, $amount, $rate, $agent_id);
        }
    }

    /**
     * 第一次代理返利
     * @param object $user
     * @param float $amount 用户充值数目
     * @param int $agent_id
     */
    private function setAgentFirstRecord($user, $amount, $rate, $agent_id)
    {
        try {
            DB::beginTransaction();

            DB::table('fund_pool')->insert([
                'member_id'    => $agent_id,
                'cash_total'   => Helper::formatMoney($amount * $rate),
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

            DB::table('capitallog')->insert([
                'user_id'        => $agent_id,
                'capital_type'   => 7,
                'rebate_user_id' => $user->id,
                'amount'         => Helper::formatMoney($amount * $rate),
                'note'           => '代理返利'.$amount * $rate,//最真实的记录到备注 其余都是舍去法保留两位
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            DB::commit();
        } catch (\Exception $e) {
            Log::error('代理'.$agent_id.'通过用户'.$user->id.'第一次代理返利失败');
            Log::error($e->getMessage());
            DB::rollBack();
        }
    }

    /**
     * 增量代理返利 非第一次
     * @param $user
     * @param $amount
     * @param $rate 代理返利回报率
     * @param $agent_id
     */
    private function incrementAgentProfit($user, $amount, $rate, $agent_id)
    {
        try {
            DB::beginTransaction();
            DB::table('fund_pool')->increment('cash_total', Helper::formatMoney($amount * $rate));

            DB::table('capitallog')->insert([
                'user_id'        => $agent_id,
                'capital_type'   => 7,
                'rebate_user_id' => $user->id,
                'amount'         => Helper::formatMoney($amount * $rate),
                'note'           => '代理返利'.$amount * $rate,//最真实的记录到备注 其余都是舍去法保留两位
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            DB::commit();
        } catch (\Exception $e) {
            Log::error('代理'.$agent_id.'通过用户'.$user->id.'代理返利失败');
            Log::error($e->getMessage());
            DB::rollBack();
        }
    }
    /**
     *  根据所找关系确定是用什么回报率
     * @param array $params
     */
    private function determinAgentRate($params)
    {
        //省市县代理都全
        if ($params['county_found'] && $params['city_found'] && $params['province_found']) {
            return [
                'province_rate' => 1/100,
                'city_rate' => 1/100,
                'county_rate' => 3/100,
            ];
        }
        //只有省和市代理
        if (!$params['county_found'] && $params['city_found'] && $params['province_found']) {
            return [
                'province_rate' => 1/100,
                'city_rate' => 4/100,
                'county_rate' => 0,
            ];
        }
        //只有省和县代理
        if ($params['county_found'] && !$params['city_found'] && $params['province_found']) {
            return [
                'province_rate' => 2/100,
                'city_rate' => 0,
                'county_rate' => 3/100,
            ];
        }
        //只有市和县代理
        if ($params['county_found'] && $params['city_found'] && !$params['province_found']) {
            return [
                'province_rate' => 0,
                'city_rate' => 1/100,
                'county_rate' => 3/100,
            ];
        }
        //只有省代理
        if (!$params['county_found'] && !$params['city_found'] && $params['province_found']) {
            return [
                'province_rate' => 5/100,
                'city_rate' => 0,
                'county_rate' => 0,
            ];
        }
        //只有市代理
        if (!$params['county_found'] && $params['city_found'] && !$params['province_found']) {
            return [
                'province_rate' => 0,
                'city_rate' => 4/100,
                'county_rate' => 0,
            ];
        }
        //只有县代理
        if ($params['county_found'] && !$params['city_found'] && !$params['province_found']) {
            return [
                'province_rate' => 0,
                'city_rate' => 0,
                'county_rate' => 3/100,
            ];
        }
    }
}
