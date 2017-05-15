<?php
/**
 * Author: yanpengcheng NYJ
 * DateTime: 2017/4/2 20:34
 * Description:
 *  全返
 *  分销返利
 *  代理返利
 */
namespace App\Http\Controllers\Api;

use App\Entity\Fund;
use App\Models\ErrorCode;
use App\Models\M3Result;
use App\Tool\Helper;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlgorithmController extends Controller
{
    /**
     * 代理人只有县级
     * 所得收益是3%
     * 不是代理区域假设为A区域则A区域代理得服务费(另算) 和此算法不参合
     * @param $user_id
     * @param $amount
     * @return string
     */
    public function calculateAgentIncome($user_id, $amount)
    {
        $user = DB::table('user')->where('id', $user_id)->first();
        if (is_null($user)) {
            Log::info('用户'.$user_id.'代理返利流程未走,未查到当前用户,用户ID='.$user_id.' 充值amount='.$amount);
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }
        //取得充值用户的代理
        $agent_id = $user->agent_id;
        if ($agent_id > 0) {
            //判定用户的区域和代理人的区域
            $agent = DB::table('agent_fund_pool')->where('agent_id', $agent_id)->first();
            if (is_null($agent)) {
                $this->setAgentFirstRecord($user, $amount, Config::get('rate.county_agent_rate'), $agent_id);
            } else {
                $this->incrementAgentProfit($user, $amount, Config::get('rate.county_agent_rate'), $agent_id);
            }
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

            DB::table('agent_fund_pool')->insert([
                'agent_id'    => $agent_id,
                'cash_total'   => Helper::formatMoney($amount * $rate),
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

            DB::table('agent_capitallog')->insert([
                'agent_id'        => $agent_id,
                'capital_type'   => 1,
                'rebate_user_id' => $user->id,
                'amount'         => Helper::formatMoney($amount * $rate),
                'note'           => '代理ID='.$agent_id.'通过用户ID='.$user->id.'第一次代理返利amount='.$amount * $rate,//最真实的记录到备注 其余都是舍去法保留两位
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            DB::commit();
        } catch (\Exception $e) {
            Log::error('代理id='.$agent_id.'通过用户'.$user->id.'第一次代理返利失败,需返利cash_total='.
                Helper::formatMoney($amount * $rate));
            Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
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
            DB::table('agent_fund_pool')->where('agent_id', $agent_id)->increment('cash_total', Helper::formatMoney($amount * $rate));

            DB::table('agent_capitallog')->insert([
                'agent_id'        => $agent_id,
                'capital_type'   => 1,
                'rebate_user_id' => $user->id,
                'amount'         => Helper::formatMoney($amount * $rate),
                'note'           => '代理ID='.$agent_id.'通过用户ID='.$user->id.'代理返利amount='.$amount * $rate,//最真实的记录到备注 其余都是舍去法保留两位
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            DB::commit();
        } catch (\Exception $e) {
            Log::error('代理id='.$agent_id.'通过用户id='.$user->id.'代理返利失败,需返利cash_total='.
                Helper::formatMoney($amount * $rate));
            Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
        }
    }
    /**
     * @param $p_userId 用户id
     * @param $p_amount 充值金额
     */
    public function fundCalculate($p_userId, $p_amount)
    {
        //1分销 充值
        $currnetUser = DB::table('user')->where('id',$p_userId)->first();
        if (is_null($currnetUser))
        {
            Log::error('用户id='.$p_userId.'充值返利流程未走,未查到当前用户,用户ID='.$p_userId.' 充值amount='.$p_amount);
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }

        $currentUserFundPool = Fund::where('member_id',$p_userId)->first();
        if ($currentUserFundPool)
        {
            $this->incrementUpstreamProfit($p_userId, $p_amount);
        } else {
            $this->fundPoolRecord($p_userId,$p_amount,$p_amount);
        }

        //分销
        if ($currnetUser && $currnetUser->p_id)
        {
            $p_fund = Fund::where('member_id',$currnetUser->p_id)->first();
            $rate1 = Config::get('rate.distribute_rate_lst');
            $p_note = '上级用户user_id='.$currnetUser->p_id.' 通过用户user_id='.$p_userId.' 分销返利amount='.$p_amount.$rate1.'失败';
            $this->setRebate($currnetUser->p_id, $p_amount, 3, $p_userId, $rate1,$p_note, $p_fund);

            //祖父级，充值获取3%返现到现金账户
            if ($currnetUser->gp_id)
            {
                $gp_fund = Fund::where('member_id',$currnetUser->gp_id)->first();
                $rate2 = Config::get('rate.distribute_rate_2nd');
                $p_note = '上上级用户user_id='.$currnetUser->gp_id.' 通过用户user_id='.$p_userId.' 分销返利amount='.$p_amount.$rate2.'失败';
                $this->setRebate($currnetUser->gp_id, $p_amount, 3, $p_userId, $rate2,$p_note, $gp_fund);
            }
        }
    }

    /**
     * 分销返利
     * @param $p_id 父级或祖父级id
     * @param $p_amount
     * @param $p_userId
     * @param $p_rate 对应的回报率
     * @param null $p_fund 父级或祖父级用户对象
     */
    private function setRebate($p_id, $p_amount,$capital_type = 3,$p_userId,$p_rate,$p_note,$p_fund = null)
    {
        if ($p_fund)
        {
            try {
                DB::beginTransaction();
                DB::table('fund_pool')
                    ->where('member_id',$p_id)
                    ->increment('cash_total', Helper::formatMoney($p_amount * $p_rate));

                DB::table('capitallog')->insert([
                    'user_id'        => $p_id,
                    'capital_type'   => $capital_type,
                    'rebate_user_id' => $p_userId,
                    'amount'         => Helper::formatMoney($p_amount * $p_rate),
                    'note'           => '上级用户user_id='.$p_id.' 通过用户user_id='.$p_userId.' 代理返利amount='.$p_amount * $p_rate,
                    'created_at'     => date('Y-m-d H:i:s'),
                    'updated_at'     => date('Y-m-d H:i:s'),
                ]);
                DB::commit();
            } catch (\Exception $e) {
                Log::error($p_note);
                Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
                DB::rollBack();
            }
        } else {
            $this->fundPoolRecord($p_id,$p_amount * $p_rate, $p_amount * $p_rate);
        }
    }
    /**
     * 全返
     * @param $p_userId
     * @param $p_amount
     */
    private function incrementUpstreamProfit($p_userId, $p_amount)
    {
        try {
            DB::beginTransaction();
            DB::table('fund_pool')
                ->where('member_id',$p_userId)
                ->update(
                    [
                        'fund_total' => DB::raw('fund_total+'.$p_amount),
                        'coupon_total' => DB::raw('coupon_total+'.$p_amount)
                    ]);

            DB::table('capitallog')->insert([
                'user_id'        => $p_userId,
                'capital_type'   => 1,
                'rebate_user_id' => $p_userId,
                'amount'         => Helper::formatMoney($p_amount),
                'note'           => '用户user_id='.$p_userId.' 充值全返购物券amount='.$p_amount,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            DB::table('capitallog')->insert([
                'user_id'        => $p_userId,
                'capital_type'   => 8,
                'rebate_user_id' => $p_userId,
                'amount'         => Helper::formatMoney($p_amount),
                'note'           => '用户user_id='.$p_userId.' 充值全返基金amount='.$p_amount,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            DB::commit();
        } catch (\Exception $e) {
            Log::error('用户user_id='.$p_userId.'充值amount='.$p_amount.' 失败');
            Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
        }

    }
    /**
     * 第一次充值没有记录就新增
     * @param $p_memberId
     * @param $p_amount
     * @param int $p_couponTotal
     */
    private function fundPoolRecord($p_memberId, $p_amount, $coupon_total)
    {
        try {
            DB::beginTransaction();
            $gp_fundPool = [
                'member_id'    => $p_memberId,
                'fund_total'   => Helper::formatMoney($p_amount),
                'coupon_total' => Helper::formatMoney($coupon_total),//比例1:1
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ];
            DB::table('fund_pool')->insert($gp_fundPool);
            DB::commit();
        } catch (\Exception $e) {
            Log::error('用户user_id='.$p_memberId.'第一次充值amount='.$p_amount.' 失败');
            Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
        }
    }
}
