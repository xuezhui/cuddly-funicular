<?php

namespace App\Http\Controllers\Api;

use App\Models\ErrorCode;
use App\Models\M3Result;
use App\Tool\Validate\Aes;
use Illuminate\Http\Request;
use App\Entity\Member;
use App\Entity\TempPhone;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Tool\Validate\Verify;
use Illuminate\Support\Facades\Hash;

/**
 * Author: yanpengcheng
 * DateTime: 2017/4/2 20:34
 * Description:
 * 会员注册、登陆、登出、不登陆修改密码、详情
 */
class MemberController extends Controller
{
    /**
     * @description 注册：自主注册和通过扫描二维码注册的逻辑
     * @param Request $request
     * @return json
     *
     */
    public function toRegister(Request $request)
    {
        $telephone = $request->input('telephone', '');
        $password = $request->input('password', '');
        $confirm = $request->input('password_confirm', '');
        $phone_code = $request->input('verify_code', '');
        $referral = $request->input('referral', '');//加密版推荐人ID
        if ($referral == '') {
            return M3Result::init(ErrorCode::$PID_INVALID);
        }
        if ($password == '' || strlen($password) < 6) {
            return M3Result::init(ErrorCode::$PASSWORD_TOO_SHORT);
        }

        if ($password != $confirm) {
            return M3Result::init(ErrorCode::$QUERYPWD_NOT_SAME_PWD);
        }
        //手机号码不合法
        if (!Verify::isPhone($telephone)) {
            return M3Result::init(ErrorCode::$TELEPHONE_INVALID);
        }
        // 手机验证码
        if ($phone_code == '' || strlen($phone_code) != 6) {
            return M3Result::init(ErrorCode::$SMS_VERIFYCODE_WRONG);
        }
        //检验用户手机号是否已经注册
        $chk = Member::where('telephone', $telephone)->first();
        if ($chk && $chk->id) {
            return M3Result::init(ErrorCode::$SMS_PHONE_EXISTS);
        }

        $tempPhone = TempPhone::where('telephone', $telephone)->first();
        if ($tempPhone == null) {
            return M3Result::init(ErrorCode::$SMS_VERIFYCODE_WRONG);
        }
        if ($tempPhone->verify_code != $phone_code) {
            return M3Result::init(ErrorCode::$SMS_VERIFYCODE_WRONG);
        }
        if ($tempPhone->verify_code == $phone_code) {
            if (time() > strtotime($tempPhone->deadline)) {
                return M3Result::init(ErrorCode::$SMS_VERIFYCODE_TIMEOUT);
            }
            $member = new Member;
            //通过二维码注册需要处理推荐人的分销关系
            $p_id = 0;
            $recommender_related_str = '';
            if ($referral) {
                $p_id = (int)Aes::letSecret($referral, 'D',config('app.referral_token'));
                if ($p_id < 0) {
                    return M3Result::init(ErrorCode::$PID_INVALID);
                }
                //根据推荐人ID查出推荐人信息
                $recommender = Member::where('id', $p_id)->first();
                if (!is_null($recommender)) {
                    $member->agent_id = $recommender->agent_id;//代理人
                    $member->p_id = $p_id;//自己的推荐人
                    $member->gp_id = $recommender->p_id;//自己推荐人的推荐人
                    $recommender_related_str = $recommender->related_str;
                }
            }

            $member->telephone = $telephone;
            $member->pay_password = Hash::make(substr($telephone, -6));
            $member->password = Hash::make($password);
            $member->save();
            //更新用户的关系字符串：如果存在推荐人ID 先取出原有的关系字符串然后拼接上自己的id
            //不存在推荐人ID就属于自主注册 关系字符串就是自己的ID
            if ($p_id > 0) {
                if ($recommender_related_str != '') {
                    $member->related_str = $recommender_related_str.'-'.$member->id;
                }
            } else {
                $member->related_str = $member->id;
            }
            $member->save();
            return M3Result::init(ErrorCode::$OK);
        }
    }

    /**
     * 用户通过接口登陆
     * @return string
     */
    public function toLogin(Request $request)
    {
        $telephone = $request->input('telephone', '');
        $password = $request->input('password', '');
        $last_login_ip = $request->getClientIp();
        if ($password == '') {
            return M3Result::init(ErrorCode::$USER_LOGIN_FAIL);
        }
        //手机号码不合法
        if (!Verify::isPhone($telephone)) {
            return M3Result::init(ErrorCode::$TELEPHONE_INVALID);
        }
        $user = Member::where('telephone', $telephone)->first();
        if ($user == null) {
            return M3Result::init(ErrorCode::$USER_LOGIN_FAIL);
        }
        if(!Hash::check($password, $user->password)) {
            return M3Result::init(ErrorCode::$USER_LOGIN_FAIL);
        }
        $user->last_login_IP = bindec(decbin(ip2long($last_login_ip)));
        $user->save();
        //session(['user' => $user]);
        //不暴露用户敏感信息
        //$userArr = $user->makeHidden('password')->toArray();
        $data = [
            'uid' => $user->id,
            'token' => Aes::letSecret($user->id, 'E', config('app.login_token')),
            'avatar' => $user->avatar,
            'telephone' => $user->telephone,
            'is_member' => $user->is_member,
            'member_level' => $user->member_level,
            'apply_progress' => $user->apply_progress,
            'status' => $user->status
        ];
        return M3Result::init(ErrorCode::$OK, $data);
    }

    /**
     * 用户退出登陆
     * @param Request $request
     */
    public function toLogout(Request $request)
    {
        //session(['user' => null]);
        //return M3Result::init(ErrorCode::$OK);
    }
    /**
     * 获取用户详情
     * @param Request $request
     */
    public function memberDetail(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $user = Member::where('id', $member_id)->first();
        if ($user == null) {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        //不暴露用户敏感信息
        $userArr = $user->makeHidden(['password', 'p_id', 'gp_id', 'agent_id', 'pay_password', 'related_str'])->toArray();
        //dd($userArr);
        return M3Result::init(ErrorCode::$OK, $userArr);
    }

    /**
     * 不登陆修改密码
     */
    public function toModPassword(Request $request)
    {
        $telephone = $request->input('telephone', '');
        $new_password = $request->input('newpassword', '');
        $confirm = $request->input('password_confirm', '');
        $phone_code = $request->input('verify_code', '');
        if ($new_password == '' || strlen($new_password) < 6) {
            return M3Result::init(ErrorCode::$PASSWORD_TOO_SHORT);
        }

        if ($new_password != $confirm) {
            return M3Result::init(ErrorCode::$QUERYPWD_NOT_SAME_PWD);
        }
        //手机号码不合法
        if (!Verify::isPhone($telephone)) {
            return M3Result::init(ErrorCode::$TELEPHONE_INVALID);
        }
        // 手机验证码
        if ($phone_code == '' || strlen($phone_code) != 6) {
            return M3Result::init(ErrorCode::$SMS_VERIFYCODE_WRONG);
        }
        $check_member = Member::where('telephone', $telephone)->first();
        if (is_null($check_member)) {
            return M3Result::init(ErrorCode::$USER_NOT_EXSIT);
        }
        $tempPhone = TempPhone::where('telephone', $telephone)->first();
        if ($tempPhone->verify_code != $phone_code) {
            return M3Result::init(ErrorCode::$SMS_VERIFYCODE_WRONG);
        }
        if ($tempPhone->verify_code == $phone_code) {
            if (time() > strtotime($tempPhone->deadline)) {
                return M3Result::init(ErrorCode::$SMS_VERIFYCODE_TIMEOUT);
            }
            $res = Member::where('telephone', $telephone)->update(['password' => Hash::make($new_password)]);
            if ($res) {
                return M3Result::init(ErrorCode::$OK);
            }
            return M3Result::init(ErrorCode::$FAIL);
        }
    }

    /**
     * 获取店铺信息
     * @param  Request $request [请求参数]
     * @return [M3Result]           [返回结果 json]
     */
    public function shopInfo(Request $request){
        $receipt = $request->input('receipt');
        if(empty($receipt)){
            return M3Result::init(ErrorCode::$SERVER_USER_INVAILD);
        }
        //校验服务会员的身份
        $receipt_id = Aes::letSecret($receipt, 'D', config('app.receipt_token'));
        $receipt_member = Member::where('id',$receipt_id)->first();
        if(!$receipt_member||$receipt_member->apply_progress!=2){
            return M3Result::init(ErrorCode::$SERVER_USER_INVAILD);
        }
        if(!empty($receipt_member->store_photos)){
            $logos = explode(',', $receipt_member->store_photos);
        }
        return M3Result::init(ErrorCode::$OK,array('storename'=>$receipt_member->store_name,'photo'=>$logos[0]));
    }
}
