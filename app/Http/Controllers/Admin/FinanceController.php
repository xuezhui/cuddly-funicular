<?php
/**
 * Author: 陈静
 * DateTime: 2017/4/3 11:30
 * Description: 财务管理
 *
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Base;
use Illuminate\Support\Facades\DB;
use App\Models\M3Result;
use App\Models\ErrorCode;
class FinanceController extends BaseController
{
    public function recharge(Request $request){
    	$query = DB::table('trade_order')->where('trade_type',1);
    	$starttime = $request->input('starttime','');
    	if(!empty($starttime)){
    		$query = $query->where('created_at','>=',$starttime.' 00:00:00');
    	}
    	$endtime = $request->input('endtime','');
    	if(!empty($endtime)){
    		$query = $query->where('created_at','<=',$endtime.' 23:59:59');
    	}
    	$records = $query->orderBy('created_at','desc')->paginate(10);
        $records->appends(['starttime'=>$starttime,'endtime'=>$endtime]);
    	foreach ($records as &$value) {
    		$value->member = array();
    		$mem = DB::table('user')->where('id',$value->user_id)->first();
    		if(!empty($mem)){
    			$value->member = $mem;
    		}
    		switch ($value->source) {
    			case 0:
    				$value->source = '未知';
    				break;
    			case 1:
    				$value->source = 'Android App';
    				break;
    			case 3:
    				$value->source = 'IOS APP';
    				break;
    			case 4:
    				$value->source = '客户端';
    				break;
    			case 5:
    				$value->source = '后台';
    				break;
    			default:
    				$value->source = '其他';
    				break;
    		}
    		switch ($value->payment_method) {
    			case 0:
    				$value->payment_method = '未知';
    				break;
    			case 1:
    				$value->payment_method = '支付宝支付';
    				break;
    			case 3:
    				$value->payment_method = '微信支付';
    				break;
    			case 4:
    				$value->payment_method = '网银支付';
    				break;
    			case 5:
    				$value->payment_method = 'apple pay';
    				break;
    			default:
    				$value->payment_method = '其他';
    				break;
    		}
    	}
    	unset($value);
    	return view('Admin/finance/recharge',['records'=>$records]);
    }
    /**
     * 服务会员提现列表
     * @param  Request $request [请求参数]
     * @return [view]           [视图]
     */
    public function smemberwithdraw(Request $request){
    	$query = DB::table('smember_withdrawal_apply');
    	$starttime = $request->input('starttime','');
    	if(!empty($starttime)){
    		$query = $query->where('created_at','>=',$starttime.' 00:00:00');
    	}
    	$endtime = $request->input('endtime','');
    	if(!empty($endtime)){
    		$query = $query->where('created_at','<=',$endtime.' 23:59:59');
    	}
        $apply_progress = $request->input('apply_progress');
        if($apply_progress!=""){
            $query = $query->where('apply_progress',$request->input('apply_progress'));
        }
    	$records = $query->orderBy('created_at','desc')->paginate(10);
    	foreach ($records as &$value) {
    		$value->member = array();
    		$mem = DB::table('user')->where('id',$value->member_id)->first();
    		if(!empty($mem)){
    			$value->member = $mem;
    		}
            //银行卡信息
            $card = DB::table('user_card')->where('id',$value->card_id)->first();
            // if($card){
                $value->card = $card;
            // }
            $value->member = DB::table('user')->where('id', $value->member_id)->first();
            if($value->apply_progress==0){
                $value->protext = '冻结';
            }elseif($value->apply_progress==1){
                $value->protext = '待审核';
            }elseif($value->apply_progress==2){
                $value->protext = '审核通过';
            }elseif($value->apply_progress==3){
                $value->protext = '审核不通过';
            }
    	}
    	unset($value);
        $apply_progress = [0 => '冻结', 1 => '待审核', 2 => '审核通过', 3 => '审核不通过'];
    	return view('Admin/finance/swithdraw',['records'=>$records,'apply_progress'=>$apply_progress]);
    }

    //处理服务会员提现
    public function swithdrawdeal(Request $request){
        $apply = DB::table('smember_withdrawal_apply')->where('id',$request->input('id'))->first();
        if(empty($apply)){
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }
        $mem = DB::table('user')->where('id',$apply->member_id)->first();
        $apply->owner = implode('/', array($mem->telephone,$mem->realname));
        if($request->method()=='POST'){
            $data['apply_progress'] = $request->input('status',0);
            $data['updated_at'] = date('Y-m-d H:i:s');
            DB::table('smember_withdrawal_apply')->where('id',$request->input('id'))->update($data);
            return M3Result::init(ErrorCode::$OK);
        }

        return view('Admin/finance/swithdrawdeal',['apply'=>$apply]);
    }

    //资金日志
    public function payLog(Request $request){
        $member_id = $request->input('id',0);
        $query = DB::table('capitallog')->where('user_id',$member_id)->whereIn('capital_type',[9,11]);
        $starttime = $request->input('starttime','');
        if(!empty($starttime)){
            $query = $query->where('created_at','>=',$starttime.' 00:00:00');
        }
        $endtime = $request->input('endtime','');
        if(!empty($endtime)){
            $query = $query->where('created_at','<=',$endtime.' 23:59:59');
        }
        $records = $query->orderBy('created_at','desc')->paginate(10);
        foreach ($records as &$value) {
            $member = DB::table('user')->where('id',$value->user_id)->first();
            $value->member = $member;
        }
        $capital_type = [
            9 => '+',
            11 => '-'
        ];
        $records->appends(['starttime'=>$starttime,'endtime'=>$endtime,'id'=>$member_id]);
        return view('Admin.finance.tpl.smemberlog',['records'=>$records,'capital_type'=>$capital_type]);
    }
}
