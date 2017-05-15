<?php
/**
 * Author: 陈静
 * DateTime: 2017/4/2 13:40
 * Description: 代理人控制器
 *
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\M3Result;
use App\Models\ErrorCode;
use App\Http\Controllers\Base;
class AgentController extends BaseController
{
    /**
     * 代理人列表
     * @param  Request $request [请求参数]
     * @return [view]           [视图]
     */
    public function index(Request $request){
    	$query = DB::table('agent');
        $agentid = $request->session()->get('admin', '')->agent_id;
        if(!empty($agentid)){
            $query = $query->where('agent.id',$agentid);
        }
    	//添加筛选条件
    	$name = $request->input('name');
    	if(!empty($name)){
    		$query = $query->where(function($tmp) use($name){
                $tmp->orWhere('agentname','like','%'.$name.'%')->orWhere('phone','like','%'.$name.'%');
            });
    	}
    	$starttime = $request->input('starttime','');
    	if(!empty($starttime)){
    		$query = $query->where('create_date','>=',strtotime($starttime." 00:00:00"));
    	}
    	$endtime = $request->input('endtime','');
    	if(!empty($endtime)){
    		$query = $query->where('create_date','<=',strtotime($endtime." 23:59:59"));
    	}
    	$agents = $query->select('agent.*',DB::raw('IFNULL(agent_fund_pool.cash_total,0.00) as cash_total'))->leftJoin('agent_fund_pool','agent_fund_pool.agent_id','=','agent.id')->orderBy('agent.id','desc')->paginate(10);
        $agents->appends(['name'=>$name,'starttime'=>$starttime,'endtime'=>$endtime]);
    	return view('Admin/agent/index',['agents'=>$agents,'request'=>$request]);
    }

    public function post(Request $request){
        $roles = DB::table("role")->select('id','name')->get();
        $agent = DB::table("agent")->where('id',$request->input('id',0))->first();
        $role = array();
        if(!empty($agent)){
            $role = DB::table("admin_role")->leftJoin('admin','admin_role.admin_id','=','admin.id')->select("admin_role.role_id")->where('admin.agent_id',$agent->id)->first();
        }
        //保存代理人数据
        if($request->method()=='POST'){
            //agent表
            $agentdata['agentname'] = $request->input('agentname','');
            $agentdata['agentaddr'] = $request->input('agentaddr','');
            $agentdata['phone'] = $request->input('phone','');
            $agentdata['note'] = $request->input('note','');
            //admin表
            $admindata['agent_id'] = $request->input('id',0);
            $admindata['mobile'] = $request->input('phone','');
            $admindata['name'] = $agentdata['agentname'];
            if(!empty($request->input('password',''))){
                $admindata['password'] = md5($request->input('password',''));
            }
            try{
                if(empty($request->input('id',0))){//新增数据
                    //判断是否已存在用户
                    $record = DB::table('admin')->where('name',$agentdata['agentname'])->orWhere('mobile',$agentdata['phone'])->get();
                    if(count($record)>0){
                        return M3Result::init(ErrorCode::$DB_ERROR);
                    }
                    //加入agent表
                    $agentdata['create_date'] = time();
                    $agent_id = DB::table('agent')->insertGetId($agentdata);
                    //加入admin表
                    $admindata['agent_id'] = $agent_id;
                    $admindata['created_date'] = time();
                    $admin_id = DB::table('admin')->insertGetId($admindata);
                    //加入admin_role 表
                    $roledata['admin_id'] = $admin_id;
                    $roledata['role_id'] = $request->input('role',0);
                    $roledata['created_date'] = time();
                    DB::table('admin_role')->insert($roledata);
                }else{//更新数据
                    //更新agent表
                    DB::table("agent")->where("id",$agent->id)->update($agentdata);
                    //更新admin表
                    // if(isset($admindata['password'])){
                        $admindata['updated_date'] = time();
                        DB::table('admin')->where('agent_id',$agent->id)->update($admindata);
                    // }
                    //更新role表
                    // if(!empty($role)){
                    //     $admin = DB::table('admin')->where('agent_id',$agent->id)->first();
                    //     $roledata['updated_date'] = time();
                    //     $roledata['role_id'] = $request->input('role',0);
                    //     DB::table('admin_role')->where('admin_id',$admin->id)->update($roledata);
                    // }
                }
            }catch(Exception $e){
                return M3Result::init(ErrorCode::$DB_ERROR);
            }
            
            return M3Result::init(ErrorCode::$OK);
        }
        return view('Admin/agent/post',['roles'=>$roles,'agent'=>$agent,'role'=>$role]);
    }

    public function captitallog(Request $request){
        $agentid = $request->input('id',0);
        $logs = DB::table('agent_capitallog')->where('agent_id',$agentid)->orderBy('created_at','desc')->paginate(10);
        foreach ($logs as &$value) {
            $member = DB::table('user')->where('id',$value->rebate_user_id)->first();
            if(!empty($member)){
                $value->member = $member->realname.'/'.$member->telephone;
            }else{
                $value->member = "结算";
            }
        }
        unset($value);
        $logs->appends(['id'=>$agentid]);
        return view('Admin/agent/logs',['logs'=>$logs]);
    }

    public function captitallogs(Request $request){
        $agent_id = $request->session()->get('admin', '')->agent_id;
        $query = DB::table('agent_capitallog')->where('agent_id',$agent_id);
        $starttime = $request->input('starttime','');
        if(!empty($starttime)){
            $query = $query->where('created_at','>=',$starttime.' 00:00:00');
        }
        $endtime = $request->input('endtime','');
        if(!empty($endtime)){
            $query = $query->where('created_at','<=',$endtime.' 23:59:59');
        }
        $logs = $query->orderBy('created_at','desc')->paginate(10);
        foreach ($logs as &$value) {
            $member = DB::table('user')->where('id',$value->rebate_user_id)->first();
            if(!empty($member)){
                $value->member = $member->realname.'/'.$member->telephone;
            }else{
                $value->member = "结算";
            }
        }
        unset($value);
        $logs->appends(['starttime'=>$starttime,'endtime'=>$endtime]);
        return view('Admin/agent/pagelogs',['logs'=>$logs]);
    }

    public function settle(Request $request){
        $agent_id = $request->input('agent_id',0);
        $agent_pool = DB::table('agent_fund_pool')->where('agent_id',$agent_id)->first();
        if($request->method()=='POST'){
            $amount = $request->input('amount',0.00);
            
            if(!$agent_pool || $agent_pool->cash_total<$amount){
                return M3Result::init(ErrorCode::$CASH_INSUFFICIENT);
            }
            try {
                DB::beginTransaction();
                //添加资金日志
                DB::table('agent_capitallog')->insertGetId([
                    'agent_id'=>$agent_id,
                    'capital_type'=>2,
                    'amount'=>$amount,
                    'note'=>'代理人 id='.$agent_id.' 结算 '.$amount.' 元',
                    'created_at'=>date('Y-m-d H:i:s')
                ]);
                
                //减少现金
                DB::table('agent_fund_pool')->where('agent_id',$agent_id)->decrement('cash_total',$amount);
                
                DB::commit();
                return M3Result::init(ErrorCode::$OK);
            } catch (\Exception $e) {
                \Log::error('代理人结算失败');
                \Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
                DB::rollBack();
                return M3Result::init(ErrorCode::$DB_ERROR);
            }
        }
        return view('Admin/agent/settle',['agent_id'=>$agent_id,'agent_pool'=>$agent_pool]);
    }

    //删除代理人
    public function delete(Request $request){
        $agent_id = $request->input('id',0);
        $adminrecord = DB::table('admin')->where('agent_id',$agent_id)->first();
        if(empty($adminrecord)||empty($agent_id)){
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }
        try {
                DB::beginTransaction();
                //删除代理表
                DB::table('agent')->where('id',$agent_id)->delete();
                //删除admin表
                DB::table('admin')->where('agent_id',$agent_id)->delete();
                //删除admin_role表
                DB::table('admin_role')->where('admin_id',$adminrecord->id)->delete();
                DB::commit();
                return M3Result::init(ErrorCode::$OK);
            } catch (\Exception $e) {
                \Log::error('删除代理人失败');
                \Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
                DB::rollBack();
                return M3Result::init(ErrorCode::$DB_ERROR);
            }
    }
}
