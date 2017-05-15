<?php
namespace App\Http\Controllers\Admin;
 
use App\Entity\CapitalLog;
use App\Http\Controllers\Controller;
use App\Tool\Validate\Verify;
use Illuminate\Http\Request;
use App\Models\M3Result;
use App\Models\ErrorCode;
use Illuminate\Support\Facades\DB;
use App\Entity\Member;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Api\FundController;
use App\Http\Controllers\Api\AlgorithmController;
/**
 *
 * 管理后台 用户管理
 * @author limin
 * @time 2017-03-29 18:00
 */
class MemberController extends BaseController
{
    /**
     * 会员列表
     * 返回会员（有条件就按条件，没条件返回全部的第一页）
     * @return type
     */
    public function index(Request $request)
    {
        $member = $request->input('member','');
        $query = DB::table('user');
        $query = $query->where('status', '<>', 0);
        if(!empty($member)){
            $query = $query->where(function($query) use($member){
                $query->orWhere('nickname','like',$member.'%')->orWhere('telephone','like',$member.'%')
                    ->orWhere('email','like',$member.'%');
            });
        }

        $starttime = $request->input('starttime');
        if(!empty($starttime)){
            $query = $query->where('created_at','>=',$starttime.' 00:00:00');
        }
        $endtime = $request->input('endtime');
        if(!empty($endtime)){
            $query = $query->where('created_at','<=',$endtime.' 23:59:59');
        }

        $count = $query->count();
        $members = $query->paginate(10);
        $members->appends(['starttime'=>$starttime]);
        $members->appends(['endtime'=>$endtime]);
        $members->appends(['member'=>$member]);
        return view('Admin.member.list',['members'=>$members, 'count'=>$count, 'request'=>$request]);
    }

    /**
     * 已删除会员列表
     * 返回已删除会员（有条件就按条件，没条件返回全部的第一页）
     * @return type
     */
    public function dellist(Request $request){

        $member = $request->input('member','');
        $query = DB::table('user');
        $query = $query->where('status', 0);
        if(!empty($member)){
            $query = $query->where(function($query) use($member){
                $query->orWhere('nickname','like',$member.'%')->orWhere('telephone','like',$member.'%')
                    ->orWhere('email','like',$member.'%');
            });
        }

        $starttime = $request->input('starttime');
        if(!empty($starttime)){
            $query = $query->where('created_at','>=',$starttime.' 00:00:00');
        }
        $endtime = $request->input('endtime');
        if(!empty($endtime)){
            $query = $query->where('created_at','<=',$endtime.' 23:59:59');
        }

        $count = $query->count();
        $members = $query->paginate(10);
        $members->appends(['starttime'=>$starttime]);
        $members->appends(['endtime'=>$endtime]);
        $members->appends(['member'=>$member]);
        return view('Admin.member.dellist',['members'=>$members, 'count'=>$count, 'request'=>$request]);
    }

    /**
     * 还原已删会员（单个或批量）
     * 返回还原状态
     * @return type
     */
    public function restore(Request $request){
        $member = Member::where('id', $request->input('id', 0))->first();
        if(empty($member)){
            $members = $request->input('ids');
            if(count($members)>0){
                foreach ($members as $value) {
                    Member::where('id',$value)->update(['status'=>1]);
                }
                return M3Result::init(ErrorCode::$OK,$members);
            }
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }
        else{
            $res = Member::where('id',$request->input('id', 0))->update(['status'=>1]);
            if($res) {
                return M3Result::init(ErrorCode::$OK);
            }
            else{
                return M3Result::init(ErrorCode::$FAIL);
            }
        }
    }

    /**
     * 会员信息页
     * 返回页面
     * @return type
     */
    public function show(Request $request){
        $id = $request->input('id', '');
        if(!empty($id)){
            $query = DB::table('user');
            $member = $query->where('id', '=', $id)->first();
            if($query){
                return view('Admin.member.tpl.show', ['member'=>$member]);
            }
        }
        return view('Admin.member.tpl.show');
    }

    /**
     * 会员编辑页
     * 返回页面
     * @return type
     */
    public function edit(Request $request){
        $id = $request->input('id', '');
        if(!empty($id)){
            $query = DB::table('user');
            $member = $query->where('id', '=', $id)->first();
            if($member){
                return view('Admin.member.tpl.edit', ['member'=>$member]);
            }
        }
    }

    /**
     * 会员编辑
     * 返回操作状态
     * @return type
     */
    public function editResult(Request $request){
        $id = $request->input('id', '');
        $realname = $request->input('realname');
        $nickname = $request->input('nickname');
        $email = $request->input('email');

        if(!empty($id)){

            $res = Member::where('id', $id)->update(['realname'=>$realname, 'nickname'=>$nickname, 'email'=>$email]);
            if($res)
                return M3Result::init(ErrorCode::$OK);
            else
                return M3Result::init(ErrorCode::$FAIL);
        }

        return M3Result::init(ErrorCode::$DATA_EMPTY);
    }

    /**
     * 密码编辑
     * 返回密码修改页面
     * @return type
     */
    public function  password(Request $request){
        $id = $request->input('id', '');
        if(!empty($id)){

            $member = Member::where('id', $id)->first();
            if($member){
                return view('Admin.member.tpl.password', ['member'=>$member]);
            }
        }
    }

    /**
     * 密码编辑
     * 返回操作状态
     * @return type
     */
    public function passwordResult(Request $request){
        $id = $request->input('id');
        $password = $request->input('newpassword');
        if(!empty($id)){

            $res = Member::where('id', $id)->update(['password'=>Hash::make($password)]);
            if($res)
                return M3Result::init(ErrorCode::$OK);
            else
                return M3Result::init(ErrorCode::$FAIL);
        }
        return M3Result::init(ErrorCode::$FAIL);
    }

    /**
     * 会员删除（删除一个或批量）
     * 返回操作状态
     * @return type
     */
    public function delete(Request $request)
    {
        $member = Member::where('id', $request->input('id', 0))->first();
        if(empty($member)){
            $members = $request->input('ids');
            if(count($members)>0){
                foreach ($members as $value) {
                    Member::where('id',$value)->update(['status'=>0]);
                }
                return M3Result::init(ErrorCode::$OK,$members);
            }
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }
        else{
            $res = Member::where('id',$request->input('id', 0))->update(['status'=>0]);
            if($res) {
                return M3Result::init(ErrorCode::$OK);
            }
            else{
                return M3Result::init(ErrorCode::$FAIL);
            }
        }
    }

    public function recharge(Request $request)
    {
        $id = $request->input('id', '');
        if(!empty($id)){

            $member = Member::where('id', $id)->first();
            if($member){
                return view('Admin.member.tpl.recharge', ['member'=>$member]);
            }
        }
    }

    public function  rechargeResult(Request $request)
    {
        $id = $request->input('id', '');
        $money = $request->input('money', 0);

        if(!empty($id)){
            $res = DB::table('trade_order')->insert(['user_id' => $id,
                                                        'status' => 1,
                                                        'trade_status' => 2,
                                                        'trade_type' => 1,
                                                        'trade_no' =>  date("YmdHis").$id.uniqid(),
                                                        'source'=> 5,
                                                        'amount' => floatval($money),
                                                        'created_at' => date('Y-m-d H:i:s'),
                                                        'updated_at' => date('Y-m-d H:i:s')]);

            if($res){
                $algorithm = new AlgorithmController;
                $algorithm->calculateAgentIncome($id, floatval($money));
                $algorithm->fundCalculate($id, floatval($money));
                return M3Result::init(ErrorCode::$OK);
            }
            else return M3Result::init(ErrorCode::$FAIL);

        }
        return M3Result::init(ErrorCode::$FAIL);
    }

    /**
     * 会员提现申请列表
     */
    public function memberWithdraw(Request $request)
    {
        $query = DB::table('member_withdrawal_apply')->where('status', '<>', 0);
        if ($request->method() == 'POST') {
            $starttime = $request->input('starttime', '');
            $endtime = $request->input('endtime', '');
            $apply_progress = $request->input('apply_progress', '');
            if (Verify::isDateTime($starttime)) {
                $query = $query->where('created_at', '>=', $starttime.' 00:00:00');
            }
            if (Verify::isDateTime($endtime)) {
                $query = $query->where('created_at', '<=', $endtime.' 23:59:59');
            }
            if ($apply_progress != '') {
                $query = $query->where('apply_progress', $apply_progress);
            }
        }
        $withdraws = $query->orderBy('created_at', 'desc')->paginate(10);
        foreach ($withdraws as $item) {
            if (isset($item->member_id)) {
                $item->member_detail = Member::where('id', $item->member_id)->first();
                //银行卡详情
                $item->card = DB::table('user_card')->where(['id' => $item->card_id, 'member_id' => $item->member_id])->first();
            }
        }
        $apply_progress = [0 => '冻结', 1 => '待审核', 2 => '审核通过', 3 => '审核不通过'];
        return view('Admin.finance.member_withdraws', compact('withdraws', 'request', 'apply_progress'));
    }
    /**
     * 审核会员提现申请
     */
    public function audit(Request $request)
    {
        $id = $request->input('id', '');
        $apply_progress = [0 => '冻结', 1 => '待审核', 2 => '审核通过', 3 => '审核不通过'];
        $member_withdrawal = DB::table('member_withdrawal_apply')->where('id', $id)->first();
        return view('Admin.finance.tpl.audit', compact('member_withdrawal', 'apply_progress'));
    }

    /**
     * 修改会员提现申请审核状态
     * @param Request $request
     */
    public function toAudit(Request $request)
    {
        $id = $request->input('id', '');
        $apply_progress = $request->input('apply_progress', 0);
        $apply_note = $request->input('apply_note', '');
        $res = DB::table('member_withdrawal_apply')->where('id', $id)->update(['apply_progress' => $apply_progress, 'apply_note' => $apply_note]);
        if ($res) {
            return M3Result::init(ErrorCode::$OK);
        }
        return M3Result::init(ErrorCode::$FAIL);
    }

    /**
     * 用户账单列表
     */
    public function singlePayLog(Request $request)
    {
        $id = $request->input('id', '');
        if (!$id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        $query = CapitalLog::where(['user_id' => $id])->whereIn('capital_type', [3,4,6])->where('status', '<>', 0);
        if ($request->method() == 'POST') {
            $starttime = $request->input('starttime', '');
            $endtime = $request->input('endtime', '');
            $keywords = $request->input('keywords', '');
            if (Verify::isDateTime($starttime)) {
                $query = $query->where('created_at', '>=', $starttime.' 00:00:00');
            }
            if (Verify::isDateTime($endtime)) {
                $query = $query->where('created_at', '<=', $endtime.' 23:59:59');
            }
            if ($keywords) {
                $query = $query->where('note', 'like', '%'.$keywords.'%');
            }
        }
        $logs = $query->orderBy('created_at', 'desc')->paginate(10);
        foreach ($logs as $item) {
            if (isset($item->user_id)) {
                $item->user = Member::where('id', $item->user_id)->first();
            }
        }
        $logs->appends(['id' => $id]);
        //来源去向：1-充值返购物券，2-消费 3-分销返利 4-提现 5-（自动）基金每天减少 6-（自动）提现每天增加
        // 7-代理返利（已独立到代理返利表） 8-充值返基金 9-服务会员服务 10-供应商 11-服务会员提现
        $capital_type = [
            3 => '+',
            4 => '-',
            6 => '+'
        ];
        return view('Admin.finance.member_singlepay', compact('logs', 'request', 'capital_type'));
    }
}
