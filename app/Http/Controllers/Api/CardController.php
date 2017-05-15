<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ErrorCode;
use App\Models\M3Result;
use Illuminate\Support\Facades\DB;
/**
 * Author: 陈静
 * DateTime: 2017/4/25 9::36
 * Description:
 * 银行卡的增删改
 */
class CardController extends Controller
{
    //新增银行卡信息
    public function add(Request $request){
    	$member_id = $request->input('uid',0);
    	$member = DB::table('user')->where('id',$member_id)->first();
    	if(empty($member)){
    		return M3Result::init(ErrorCode::$INVALID_USER_ID);
    	}
    	$realname = $request->input('owner','');
    	$bank = $request->input('bank','');
    	$card_number = $request->input('cardNum','');
    	if(empty($realname)||empty($bank)||empty($card_number)){
    		return M3Result::init(ErrorCode::$CARD_INVALID);
    	}
    	//判断是否重复添加卡号
    	$record = DB::table('user_card')->where('member_id',$member_id)->where('card_number',$card_number)->whereNull('status')->first();
    	if(!empty($record)){
    		return M3Result::init(ErrorCode::$DUPLICATE_CARD);
    	}
    	DB::table('user_card')->insert([
    		'member_id'=>$member_id,
    		'realname'=>$realname,
    		'bank' => $bank,
    		'card_number'=>$card_number,
    		'created_at' => time()
    	]);
    	return M3Result::init(ErrorCode::$OK);
    }

    //编辑银行卡
    public function edit(Request $request){
    	$member_id = $request->input('uid',0);
    	$member = DB::table('user')->where('id',$member_id)->first();
    	if(empty($member)){
    		return M3Result::init(ErrorCode::$INVALID_USER_ID);
    	}
    	$card_id = $request->input('cardId',0);
    	$card = DB::table('user_card')->where('member_id',$member_id)->where('id',$card_id)->whereNull('status')->first();
    	if(empty($card)){
    		return M3Result::init(ErrorCode::$CARD_INVALID);
    	}
    	$realname = $request->input('owner','');
    	$bank = $request->input('bank','');
    	$card_number = $request->input('cardNum','');
    	if(empty($realname)||empty($bank)||empty($card_number)){
    		return M3Result::init(ErrorCode::$CARD_INVALID);
    	}
    	DB::table('user_card')->where('id',$card_id)->update([
    		'realname'=>$realname,
    		'bank' => $bank,
    		'card_number'=>$card_number,
    		'updated_at' => time()
    	]);
    	return M3Result::init(ErrorCode::$OK);
    }

    //删除银行卡
    public function delete(Request $request){
    	$member_id = $request->input('uid',0);
    	$member = DB::table('user')->where('id',$member_id)->first();
    	if(empty($member)){
    		return M3Result::init(ErrorCode::$INVALID_USER_ID);
    	}
    	$card_id = $request->input('cardId',0);
    	$card = DB::table('user_card')->where('member_id',$member_id)->where('id',$card_id)->whereNull('status')->first();
    	if(empty($card)){
    		return M3Result::init(ErrorCode::$CARD_INVALID);
    	}
    	DB::table('user_card')->where('id',$card_id)->update(['status'=>-1]);
    	return M3Result::init(ErrorCode::$OK);
    }

    //银行卡列表
    public function listcards(Request $request){
    	$member_id = $request->input('uid',0);
    	$member = DB::table('user')->where('id',$member_id)->first();
    	if(empty($member)){
    		return M3Result::init(ErrorCode::$INVALID_USER_ID);
    	}
    	$cards = DB::table('user_card')->select('id','realname','bank','card_number')->where('member_id',$member_id)->whereNull('status')->orderBy('created_at','desc')->paginate(20);

    	return M3Result::init(ErrorCode::$OK,$cards);
    }

    //最新一条银行卡记录
    public function newest(Request $request){
    	$member_id = $request->input('uid',0);
    	$member = DB::table('user')->where('id',$member_id)->first();
    	if(empty($member)){
    		return M3Result::init(ErrorCode::$INVALID_USER_ID);
    	}
    	$record = DB::table('smember_withdrawal_apply')->where('member_id',$member_id)->orderBy('created_at','desc')->first();
    	$record1 = DB::table('member_withdrawal_apply')->where('member_id',$member_id)->orderBy('created_at','desc')->first();
    	if(!$record && !$record1){
    		$card = DB::table('user_card')->select('id','realname','bank','card_number')->where('member_id',$member_id)->whereNull('status')->orderBy('created_at','desc')->first();
    	}else{
    		if($record && !$record1){

    		}elseif(!$record && $record1){
    			$record = $record1;
    		}else{
    			if($record->created_at<$record1->created_at){
		    		$record = $record1;
		    	}
		    }
		    $card = DB::table('user_card')->select('id','realname','bank','card_number')->where('id',$record->card_id)->whereNull('status')->first();
    	}
    	return M3Result::init(ErrorCode::$OK,$card);
    }
}
