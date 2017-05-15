<?php

namespace App\Http\Controllers\Admin;

use App\Entity\CapitalLog;
use App\Entity\Member;
use App\Models\ErrorCode;
use App\Models\M3Result;
use App\Tool\Validate\Verify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Base;

class SmemberController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Member::where(['member_level' => 1])->where('status', '<>', 0);
        if ($request->method() == 'POST') {
            $starttime = $request->input('starttime', '');
            $endtime = $request->input('endtime', '');
            $keywords = $request->input('keywords', '');
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
            if ($keywords) {
                $query = $query->where('nickname', 'like', '%'.$keywords.'%')
                    ->orWhere('store_name', 'like', '%'.$keywords.'%')
                    ->orWhere('telephone', 'like', '%'.$keywords.'%');
            }
        }
        $members = $query->orderBy('created_at', 'desc')->paginate(10);
        $apply_progress = [0 => '冻结', 1 => '待审核', 2 => '审核通过', 3 => '审核不通过'];
        return view('Admin.member.smember_list', compact('members', 'request', 'apply_progress'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $member = Member::where('id', $id)->first();
        return view('Admin.member.tpl.smember_edit', compact('member'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->input('id', '');
        $update = $request->except(['id', 'editormd-html-code']);
        $res = Member::where('id', $id)->update($update);
        if ($res) {
            return M3Result::init(ErrorCode::$OK);
        }
        return M3Result::init(ErrorCode::$FAIL);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->id;
        $res = Member::where('id', $id)->update(['status' => 0]);
        if ($res) {
            return M3Result::init(ErrorCode::$OK);
        }
        return M3Result::init(ErrorCode::$FAIL);
    }

    /**
     * 批量删除
     * @param Request $request
     * @return string
     */
    public function batchDestroy(Request $request)
    {
        $ids = $request->ids;
        $res = Member::whereIn('id', $ids)->update(['status' => 0]);
        if ($res) {
            return M3Result::init(ErrorCode::$OK);
        }
        return M3Result::init(ErrorCode::$FAIL);
    }
    /**
     * 审核服务会员申请
     */
    public function audit(Request $request)
    {
        $id = $request->input('id', '');
        $apply_progress = [0 => '冻结', 1 => '待审核', 2 => '审核通过', 3 => '审核不通过'];
        $member = Member::where('id', $id)->first();
        return view('Admin.member.tpl.audit', compact('member', 'apply_progress'));
    }

    /**
     * 修改审核状态
     * @param Request $request
     */
    public function toAudit(Request $request)
    {
        $id = $request->input('id', '');
        $apply_progress = $request->input('apply_progress', 0);
        $apply_note = $request->input('apply_note', '');

        try
        {
            DB::beginTransaction();
            Member::where('id', $id)->update(['apply_progress' => $apply_progress, 'apply_note' => $apply_note]);
            $record = DB::table('fund_pool')->where('member_id',$id)->first();
            if(!$record){
                $fund_pool_record['member_id'] = $id;
                $fund_pool_record['srv_gold'] = 0;
                $fund_pool_record['created_at'] = date('Y-m-d H:i:s');
                $fund_pool_record['updated_at'] = date('Y-m-d H:i:s');
                $fund_pool_record['deal_count'] = 0;
                DB::table('fund_pool')->insert($fund_pool_record);
            }

            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        }catch (\Exception $e) {
            DB::rollBack();
        }
        return M3Result::init(ErrorCode::$FAIL);
    }

    /**
     * 商户单个支付列表
     */
    public function singlePayLog(Request $request)
    {
        $id = $request->input('id', '');
        if (!$id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        $query = CapitalLog::where(['user_id' => $id, 'capital_type' => 9])->where('status', '<>', 0);
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
        return view('Admin.member.smember_singlepay', compact('logs', 'request'));
    }
    /**
     * 服务会员支付列表
     * @param Request $request
     */
    public function payList(Request $request)
    {
        $query = CapitalLog::where(['capital_type' => 9])->where('status', '<>', 0);
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
        $logs = $query->paginate(10);
        foreach ($logs as $item) {
            if (isset($item->user_id)) {
                $item->user = Member::where('id', $item->user_id)->first();
            }
        }
        return view('Admin.member.smember_paylist', compact('logs', 'request'));
    }
}
