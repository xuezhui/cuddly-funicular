<?php
/**
 * Created by PhpStorm.
 * User: NYJ
 * Date: 2017/3/23
 * Time: 9:51
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Service\UploadController;
use Illuminate\Http\Request;
use App\Models\M3Result;
use App\Models\ErrorCode;
use App\Entity\User;
use App\Entity\SMSVerify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mail;

class UserController extends Controller
{
    
    public function getAllowAmount(){
        return M3Result::init(ErrorCode::$OK,config('allowAmount'));
    }


    /**
     * @param Request $request
     * @return json data
     * 服务会员列表
     */
    public function getServerUsers(Request $request)
    {
        $query = DB::table('user as u')
            ->leftJoin('fund_pool as fp', 'fp.member_id', '=', 'u.id')
            ->where(['u.member_level' => 1, 'u.apply_progress' => 2]);
        $query->where('u.status','<>',0);
        $query->select('u.id','u.main_products','u.store_phones','u.store_name','u.address','u.store_photos','u.store_introduction','fp.deal_count');
        $stores = $query->paginate(20);
        return M3Result::init(ErrorCode::$OK,$stores);
    }

    /**
     * @param Request 请求参数，服务会员id
     * @return string
     * 服务会员详情
     */
    public function serveDetail(Request $request)
    {
        $nUserId = $request->get('uid', '');
        $userModel = DB::table('user')
            ->where('user.id',$nUserId)
            ->where('status','<>',0)
            ->select('id','main_products','store_phones','store_name','address','store_photos','store_introduction')
            ->first();

        if(is_null($userModel))
        {
            return M3Result::init(ErrorCode::$DEVICE_PLS_USER_OR_PHONE);
        }
        return M3Result::init(ErrorCode::$OK,$userModel);

    }


    public function charge(Request $request)
    {
        $chargeAmount = $request->input('amount');//单位为 分
        $nUserId      = $request->input('uid');
        $order_no     = date("YmdHis").$nUserId.uniqid();

        $allowAmount = config('allowAmount');
        if (!in_array($chargeAmount,$allowAmount))
        {
            return M3Result::init(ErrorCode::$NOT_ALLOW_AMOUNT);
        }

        $checkUser = User::where('id',$nUserId)->first();
        if (is_null($checkUser))
        {
            return M3Result::init(ErrorCode::$USER_NOT_EXSIT);
        }

        //获取通联用户id//新注册用户
        $re = require_once (base_path()."/vendor/gtpay/PayUtil.php");
        $merchantId    = config('app.h5merchid');
        $key           = config('app.h5merchkey');
        $pay           = new \PayUtil($merchantId,$key);
        $result        = $pay->getTLUserid($nUserId);

        if($result['status'])
        {
            $userId = $result['userId'];
        }else{
            return M3Result::init(ErrorCode::$REGISTRT_FAIL_IN_PAY);
        }

        $ext1 = "<USER>".$userId."</USER>";
        //处理代码
        /* 交易完成后页面即时通知跳转的URL  */
        $return_url = 'http://'.$request->server('SERVER_NAME').'/api/charge/returnUrl';
        /* 接收后台通知的URL */
        $notify_url = 'http://'.$request->server('SERVER_NAME').'/api/charge/notify';
        /* 货币代码，人民币：CNY    */
        $currency_type = 'CNY';
        /*清算货币代码，人民币：CNY */
        $sett_currency_type = 'CNY';
        $orderDatetime = date('YmdHis');
        $redirecturl = $pay->getRedirectUrl($return_url,$notify_url,$order_no,$chargeAmount*100,$orderDatetime,$ext1);

        $gp_userLog = [
            'user_id'      => $nUserId,
            'trade_status' => 1,//默认状态1：0-删除，1-未支付，2-已支付
            'trade_type'   => 1,
            'source'       => 4,
            'amount'       => $chargeAmount,
            'trade_no'     => $order_no,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        DB::table('trade_order')->insert($gp_userLog);

        return M3Result::init(ErrorCode::$OK,['requestUrl' =>$redirecturl]);

    }

    /**
     * @return string 特别注意！！！！
     * 注意订单不要重复处理
     * 注意判断返回金额是否与本系统金额相符
     * 处理业务完毕
     */
    public function returnUrl()
    {
        
        return redirect(config('app.pay_return'));
    }

    public function notify()
    {
        //测试商户的key! 请修改。
        $merchantId = config('app.h5merchid');
        $key = config('app.h5merchkey');
        $re = require_once (base_path()."/vendor/gtpay/PayUtil.php");
        $pay = new \PayUtil($merchantId,$key);

        $paymentOrderId = $_POST['paymentOrderId'];
        $orderNo        = $_POST['orderNo'];
        $orderDatetime  = $_POST['orderDatetime'];
        $orderAmount    = $_POST['orderAmount'];
        $payDatetime    = $_POST['payDatetime'];
        $payAmount      = $_POST['payAmount'];
        $ext1           = $_POST['ext1'];
        $payResult      = $_POST['payResult'];
        $returnDatetime = $_POST['returnDatetime'];
        $signMsg        = $_POST["signMsg"];

        //此处的orderAmount 可以通过orderNo在商城订单中查询订单金额(单位为 分)传入该方法，防止支付金额被篡改后订单的状态还能被更新为"支付成功"
        $verfiyResult = $pay->payResult($paymentOrderId,$orderNo,$orderDatetime,$orderAmount,$payDatetime,$payAmount,$ext1,$payResult,$returnDatetime,$signMsg);

        if($verfiyResult){
            $verify_Result_0 = "报文验签成功!";
        }
        else{
            $verify_Result_0 = "报文验签失败!";
        }

        $content = '';
        if($payResult == 1)
        {
            $pay_Result_0 = "订单支付成功！";

            //处理业务开始
            $content .= "\n 获取异步通知信息成功!\n\n";
            $content .= " success "."\n\n";
            $content .= "业务代码：".$paymentOrderId."\n";
            $content .= "商户号：".$paymentOrderId."\n";
            $content .= "终端号：".$paymentOrderId."\n";
            $content .= "商户系统订单号：".$orderNo."\n";
            $content .= "网关系统支付号：".$payAmount."\n";
            $content .= "订单金额：".$orderAmount."\n";
            $content .= "支付结果（1表示成功）：".$payResult."\n";
            $content .= "支付时间：".$payDatetime."\n";
            $content .= "清算日期：".$orderDatetime."\n";
            $content .= "清算时间：".$signMsg."\n";
            $content .= "ext1：".$ext1."\n";
            //$content .= "签名类型：".$sign_type."\n";
            $content .= "签名：".$signMsg."\n";
        } else {
            $pay_Result_0 = "订单支付失败！";
        }

        if($verfiyResult && $payResult == 1)
        {
            $_tmp_trade_order = DB::table('trade_order')->where('trade_no',$orderNo)->first();
            if (is_null($_tmp_trade_order) || $_tmp_trade_order->trade_status==2)
            {
                return M3Result::init(ErrorCode::$DATA_EMPTY);
            }

            if ($_tmp_trade_order->amount * 100 != $orderAmount )
            {
                
                return M3Result::init(ErrorCode::$AMOUNT_POSSIBLE_TAMPER);
            }

            //更新充值订单表
            DB::table('trade_order')->where('trade_no',$orderNo)->update(['trade_status' =>2]);

            $_o_Algorithm = new AlgorithmController();
            $reAgent = $_o_Algorithm->calculateAgentIncome($_tmp_trade_order->user_id,$_tmp_trade_order->amount);
            return $_o_Algorithm->fundCalculate($_tmp_trade_order->user_id,$_tmp_trade_order->amount);
        } else {
            $content .= "\n" . "验证签名失败" ;
        }

        dd('success');

    }

    /**
     * [getRecommend 推荐人api]
     * @param  Request $request [请求数据对象]
     * @return [type]           [接口返回处理结果]
     */
    public function getRecommend(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $member = User::where('id', $member_id)->first();
        if(empty($member))
        {
            return M3Result::init(ErrorCode::$ONLY_USER_ALLOW);
        }
        if ($member->p_id == 0) {
            return M3Result::init(ErrorCode::$NO_RECOMMENDER);
        }
        $user = User::where('id', $member->p_id)->first();
        if ($user == null) {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        //不暴露用户敏感信息
        $userArr = $user->makeHidden('password')->toArray();
        return M3Result::init(ErrorCode::$OK, $userArr);

    }

    public function uploadAvatar(Request $request)
    {
        if($request->method() != 'POST')
        {
            return M3Result::init(ErrorCode::$ONLY_POST_REQUEST);
        }
        $uploadObj = new UploadController();
        return $uploadObj->uploadFile($request,'jpg');

    }

    public function uploadLogo(Request $request)
    {
        if($request->method() != 'POST')
        {
            return M3Result::init(ErrorCode::$ONLY_POST_REQUEST);
        }
        $uploadObj = new UploadController();
        return $uploadObj->uploadFile($request,'logo');
    }

    public function update(Request $request)
    {
        $nUserId = $request->input('uid');
        if (is_null($nUserId))
        {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }

        $userModel = User::where('id',$nUserId)->first();
        if (is_null($userModel))
        {
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }

        $query = DB::table('user')->where('id',$nUserId);
        $_tmp = [];
        if ($strNickname = $request->input('nickname'))
        {
            $_tmp['nickname'] = $strNickname;
        }

        if ($strRealname = $request->input('realname'))
        {
            $_tmp['realname'] = $strRealname;
        }

        if ($strAvatar = $request->input('avatar'))
        {
            $_tmp['avatar'] = $strAvatar;
        }

        //修改密码（登录状态下）
        if ($strOldPasswd = $request->input('old_password'))
        {
            if (!Hash::check($strOldPasswd, $userModel->password))
            {
                return M3Result::init(ErrorCode::$USER_WRONG_OLD_PWD);
            }

            if ($request->input('new_password') != $request->input('re_new_password'))
            {
                return M3Result::init(ErrorCode::$QUERYPWD_NOT_SAME_PWD);
            }
            $_tmp['password'] = Hash::make($request->input('new_password'));
        }

        //修改支付密码
        if ($strOldPayPasswd = $request->input('old_pay_password'))
        {
            if (!Hash::check($strOldPayPasswd, $userModel->pay_password))
            {
                return M3Result::init(ErrorCode::$USER_WRONG_OLD_PWD);
            }

            if ($request->input('new_pay_password') != $request->input('re_new_pay_password'))
            {
                return M3Result::init(ErrorCode::$QUERYPWD_NOT_SAME_PWD);
            }
            $_tmp['pay_password'] = Hash::make($request->input('new_pay_password'));

        }

        try {
            $re = $query->update($_tmp);
        } catch (Exception $error) {
            return M3Result::init(ErrorCode::$DB_ERROR,$error);
        }

        if ($re)
        {
            //获取最新用户数据
            $userModel = User::where('id',$nUserId)->first();
            return M3Result::init(ErrorCode::$OK,$userModel);
        }
        return M3Result::init(ErrorCode::$NO_CHANGE_FOUND);

    }

    public function detail(Request $request)
    {
        $nUserId = $request->get('uid', '');
        $userModel = DB::table('user')
            ->leftJoin('fund_pool', 'user.id', '=', 'fund_pool.member_id')
            ->where('user.id',$nUserId)
            ->first();

        if(is_null($userModel))
        {
            return M3Result::init(ErrorCode::$DEVICE_PLS_USER_OR_PHONE);
        }
        return M3Result::init(ErrorCode::$OK,$userModel);
    }

    public function applyProvider(Request $request)
    {
        $user_id = $request->input('uid');

        if(empty($user_id)){
            return M3Result::init(ErrorCode::$SERVER_USER_INVAILD);
        }

        $user = User::where('id', $user_id)->first();
        if(!empty($user)){
            if($user->member_level == 1){
                if($user->apply_progress == 1)
                    return M3Result::init(ErrorCode::$SERVER_USER_PEDDING);
                else if($user->apply_progress == 2)
                    return M3Result::init(ErrorCode::$SERVER_USER_ALREADY);
            }

            $applicant = $request->input('applicant');
            $store_name = $request->input('store_name');
            $store_introduction = $request->input('store_introduction');
            $store_address = $request->input('address');
            $store_phones = $request->input('store_phones');
            $store_photos = $request->input('store_photos');
            $main_products = $request->input('main_products');

            $photos = explode(',', $store_photos);

            $count = count($photos);
            for($i=0; $i< $count; $i++){
                if(empty($photos[$i])){
                    unset($photos[$i]);
                }
            }

            $store_photos_new = implode(',', $photos);

            $res = User::where('id', $user_id)->update(['applicant' => $applicant,
                                                        'store_name' => $store_name,
                                                        'store_introduction' => $store_introduction,
                                                        'address' => $store_address,
                                                        'store_photos' => $store_photos_new,
                                                        'store_phones' => $store_phones,
                                                        'main_products' => $main_products,
                                                        'apply_progress' => 1,
                                                        'member_level' => 1]);

            if(!empty($res)){
                return M3Result::init(ErrorCode::$OK);
            }
            return M3Result::init(ErrorCode::$DB_ERROR);
        }
        return M3Result::init(ErrorCode::$USER_NOT_EXSIT);
    }
}
