<?php
/**
 * Author: 陈静
 * DateTime: 2017/4/2 17:05
 * Description: 代理-会员控制器
 *
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\M3Result;
use App\Models\ErrorCode;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Base;
class AgentMemberController extends BaseController
{
	/**
	 * 会员列表
	 * @param  Request $request [请求参数]
	 * @return [view]           [视图]
	 */
    public function index(Request $request){
    	$agent_id = $request->session()->get('admin', '')->agent_id;
        $query = DB::table('user')->where('p_id',0);
        if(!empty($agent_id)){
            $query = $query->where('agent_id',$agent_id);
        }
    	
    	$member = $request->input('member','');
    	if(!empty($member)){
    		$query = $query->where(function($tmp) use($member){
    			$tmp->orWhere('nickname','like','%'.$member.'%')->orWhere('telephone','like','%'.$member.'%')->orWhere('email','like','%'.$member.'%');
    		});
    	}
    	$starttime = $request->input('starttime','');
    	if(!empty($starttime)){
    		$query = $query->where('created_at','>=',$starttime.' 00:00:00');
    	}
    	$endtime = $request->input('endtime','');
    	if(!empty($endtime)){
    		$query = $query->where('created_at','<=',$endtime.' 23:59:59');
    	}
    	$members = $query->paginate(10);
        $members->appends(['member'=>$member,'starttime'=>$starttime,'endtime'=>$endtime]);
    	return view('Admin/agentmember/index',['members'=>$members,'request'=>$request]);
    }
    /**
     * 编辑/添加会员
     * @param  Request $request [请求数据]
     * @return [view]           [视图]
     */
    public function post(Request $request){
    	$agent_id = $request->session()->get('admin', '')->agent_id;
    	$member = DB::table('user')->where('id',$request->input('id',0))->where('agent_id',$agent_id)->first();
    	if($request->method()=='POST'){
    		$data['realname'] = $request->input('realname','');
    		$data['nickname'] = $request->input('nickname','');
    		$data['telephone'] = $request->input('mobile','');
    		if(!empty($request->input('password'))){
    			$data['password'] = Hash::make($request->input('password',''));
    		}
    		$data['email'] = $request->input('email','');
    		$data['agent_id'] = $agent_id;
    		if(empty($member)){//新增数据
    			//手机是否重复
    			$record = DB::table('user')->where('telephone',$data['telephone'])->orWhere('email',$data['email'])->get();
    			if(count($record)>0){
    				return M3Result::init(ErrorCode::$DB_ERROR);
    			}
                $data['pay_password'] = Hash::make(substr($data['telephone'],-6));
    			$data['created_at'] = date('Y-m-d H:i:s');
    			$uid = DB::table('user')->insertGetId($data);
                DB::table('user')->where('id',$uid)->update(['related_str'=>$uid]);
    		}else{
    			$data['updated_at'] = date('Y-m-d H:i:s');
                $data['related_str'] = $member->id;
    			DB::table('user')->where('id',$member->id)->update($data);
    		}
    		return M3Result::init(ErrorCode::$OK);
    	}
    	return view('Admin/agentmember/post',['member'=>$member]);
    }

    public function delete(Request $request){
    	$agent_id = $request->session()->get('admin', '')->agent_id;
    	$member = DB::table('user')->where('id',$request->input('id',0))->where('agent_id',$agent_id)->first();
    	if(empty($member)){
    		//批量删除
    		$result = DB::table('user')->whereIn('id',$request->input('ids',[]))->where('agent_id',$agent_id)->delete();
    		if(!empty($result)){
	    		return M3Result::init(ErrorCode::$OK);
    		}
    		return M3Result::init(ErrorCode::$DATA_EMPTY);
    	}else{
    		
			DB::table('user')->where('id',$member->id)->delete();
			return M3Result::init(ErrorCode::$OK);
    	}
    }
}
