<?php
namespace App\Http\Controllers\Api;

use App\Entity\CapitalLog;
use App\Entity\Member;
use App\Entity\Fund;
use App\Models\ErrorCode;
use App\Models\M3Result;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
/**
 * Author: yanpengcheng
 * DateTime: 2017/4/2 20:34
 * Description:
 * 账单
 */
class FundController extends Controller
{
    private $notIn = [9, 10, 11];//服务会员收款
    /**
     * @description 接口提供会员id的字符串 返回会员资产总览的情况
     * @param Request $request
     * @return json
     */
    public function getMemberFunds(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $funds = Fund::where('member_id', $member_id)->first();
        if (is_null($funds)) {
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }
        return M3Result::init(ErrorCode::$OK, $funds->toArray());
    }

    /**
     * 用户账单接口（可参考支付宝账单）
     * 分页每页倒序显示20条
     * 如果是购物券购买商品 需要显示商品的配送状态
     * 人性化时间显示：今天 昨天 的显示为'今天''昨天'
     * 昨天之前的都显示为周几和日期
     */
    public function memberBill(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $week = [
            1 => '一', 2 => '二', 3 => '三',
            4 => '四', 5 => '五', 6 => '六',
            7 => '日',
        ];
        //账单筛选
        $data = $this->generateBillSearch($request, $member_id);
        foreach ($data as $item) {
            //dd($item->created_at->dayOfWeek);
            if (is_object($item->created_at)) {
                //dd($item->created_at->format('m-d'));
                //今天
                if ($item->created_at->toDateString() == date('Y-m-d')) {
                    $item->human_date = '今天';
                } else if ($item->created_at->toDateString() == date('Y-m-d', strtotime('-1 day'))) {
                    $item->human_date = '昨天';
                } else {
                    $item->human_date = '周'.$week[$item->created_at->dayOfWeek];
                }
                $item->date = $item->created_at->format('m-d');
            }
        }
        return M3Result::init(ErrorCode::$OK, $data->toArray());
    }

    /**
     * 构建账单搜索结果
     * @param $request
     * @param $member
     * @return mixed
     */
    private function generateBillSearch($request, $member_id)
    {
        $capital_type = $request->input('capital_type', '');
        $keywords = $request->input('keywords', '');
        $year = $request->input('year', '');//年
        $month = $request->input('month', '');//月
        $query = CapitalLog::where('user_id', $member_id)->whereNotIn('capital_type', $this->notIn);
        if ($capital_type != '') {
            $capital_type = explode(',', $capital_type);
            $query = $query->whereIn('capital_type', $capital_type);
        }

        if ($year != '' && $month != '') {
            $date = $year.'-'.$month;
            //月首
            $month_start = date('Y-m-d 00:00:00', strtotime($date));
            //月末
            $month_end = date('Y-m-d 23:59:59', strtotime($date.'-'.date('t', strtotime($date))));
            $query = $query->whereBetween('created_at', [$month_start, $month_end]);
        }
        if ($keywords != '') {
            $query = $query->where('note', 'like', '%'.$keywords.'%');
        }
        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * 服务会员交易列表
     * @param  Request $request [请求参数]
     * @return [M3Result]           [结果]
     */
    public function turnover(Request $request){
        $member_id = $request->input('uid');
        if(empty($member_id)){
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $logs = CapitalLog::where('user_id',$member_id)->whereIn('capital_type',[9,11])->orderBy('created_at','desc')->paginate(20);
        $logArr = array();
        foreach ($logs as $value) {
            $datetime = strtotime(date('Y-m-d',strtotime($value->created_at)));
            $delta = time() - $datetime;
            $log = array();
            if($delta<=86400){//今天
                $log['date'] = '今天';
            }elseif($delta<=86400*2){
                $log['date'] = '昨天';
            }elseif($delta<=86400*3){
                $log['date'] = '前天';
            }else{
                $log['date'] = date('Y-m-d',strtotime($value->created_at));
            }
            $log['time'] = date('H:i',strtotime($value->created_at));
            $rebate_member = Member::where('id',$value->rebate_user_id)->first();
            if(!empty($rebate_member)){
                $log['from'] = $rebate_member->nickname;
            }
            
            $log['amount'] = $value->amount;
            $log['type'] = $value->capital_type;
            $logArr[] = $log;
        }
        return M3Result::init(ErrorCode::$OK, $logArr);
    }

    /**
     * 会员提现申请
     */
    public function memberWithdrawalApply(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $card_id = $request->input('card_id', '');
        $amount = $request->input('amount', 0.00);
        if (!$amount) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if (!$card_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if ($amount % 100 != 0 || $amount<=0) {
            return M3Result::init(ErrorCode::$WITHDRAWAL_CASH_INVALID);
        }
        //银行卡归属判断
        $card = DB::table('user_card')->where(['member_id' => $member_id, 'id' => $card_id])->whereNull('status')->first();
        if (empty($card)) {
            return M3Result::init(ErrorCode::$CARD_INVALID);
        }
        $cash_total = Fund::where('member_id', $member_id)->value('cash_total');
        if ($cash_total <= $amount) {
            return M3Result::init(ErrorCode::$CASH_INSUFFICIENT);
        }

        try {
            DB::beginTransaction();
            //写入流水日志
            $capitallog_id = DB::table('capitallog')->insertGetId([
                'user_id'        => $member_id,
                'capital_type'   => 4,//提现
                'amount'         => $amount,
                'note'           => '购物券消费'.$amount,//最真实的记录到备注 其余都是舍去法保留两位
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            DB::table('member_withdrawal_apply')->insert([
                'capitallog_id' => $capitallog_id,
                'member_id' => $member_id,
                'apply_progress' => 1,//申请进度 0:冻结 1:待审核 2:审核通过  3:审核不通过
                'card_id' => $card_id,
                'amount' => $amount,
                'created_at' => date('Y-m-d H:i:s', time())
            ]);
            //扣除用户提现金额
            $res = DB::table('fund_pool')->where('member_id', $member_id)->decrement('cash_total', $amount);
            DB::commit();
            if ($res) {
                return M3Result::init(ErrorCode::$OK);
            }
            return M3Result::init(ErrorCode::$FAIL);
        } catch (\Exception $e) {
            Log::error('用户id='.$member_id.' 提现申请失败提现额度='.$amount);
            Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
        }
    }

    /**
     * 服务会员提现申请
     */
    public function SMemberwithdraw(Request $request){
        $member_id = $request->input('uid');
        $member = DB::table("user")->where('id',$member_id)->first();
        if(empty($member)){
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        if($member->member_level!=1 || $member->apply_progress!=2){
            return M3Result::init(ErrorCode::$SERVER_USER_INVAILD);
        }
        $fund_pool = Fund::where('member_id',$member_id)->first();
        if(empty($fund_pool)){
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $amount = $request->input('amount',0.00);
        if($fund_pool->srv_gold<$amount){
            return M3Result::init(ErrorCode::$CASH_INSUFFICIENT);
        }
        if($amount%100!=0||$amount<=0){
            return M3Result::init(ErrorCode::$WITHDRAWAL_CASH_INVALID);
        }
        //银行卡归属判断
        $card_id = $request->input('cardId',0);
        $card = DB::table('user_card')->where('member_id',$member_id)->where('id',$card_id)->whereNull('status')->first();
        if(empty($card)){
            return M3Result::init(ErrorCode::$CARD_INVALID);
        }
        try {
            DB::beginTransaction();
            //添加资金日志
            $capitallogid = DB::table('capitallog')->insertGetId([
                'user_id'=>$member_id,
                'capital_type'=>11,
                'amount'=>$amount,
                'note'=>'服务会员 id='.$member_id.' 提现 '.$amount.' 元',
                'created_at'=>date('Y-m-d H:i:s')
            ]);
            //添加支付申请
            DB::table('smember_withdrawal_apply')->insert([
                'member_id'=>$member_id,
                'apply_progress'=>1,
                'amount'=>$amount,
                'card_id'=>$card_id,
                'capitallog_id'=>$capitallogid,
                'created_at'=>date('Y-m-d H:i:s')
            ]);
            //减少积分
            DB::table('fund_pool')->where('member_id',$member_id)->decrement('srv_gold',$amount);

            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        } catch (\Exception $e) {
            \Log::error('用户提现失败');
            \Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
            return M3Result::init(ErrorCode::$DB_ERROR);
        }
    }
}
