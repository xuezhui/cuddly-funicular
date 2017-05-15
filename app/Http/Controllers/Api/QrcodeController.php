<?php

namespace App\Http\Controllers\Api;

use App\Tool\Validate\Aes;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ErrorCode;
use App\Models\M3Result;
/**
 * Author: yanpengcheng
 * DateTime: 2017/4/2 20:34
 * UpdateTime: 2017/4/17 15:45 Updated by 陈静
 * Description:
 * 生成推广二维码、服务会员收款二维码
 */
class QrcodeController extends Controller
{
    /**
     * 生成并返回推广二维码
     * 链接地址包括用户的id
     * @param referral 推荐人的id （就是自己的id）采用Aes的自定义加密方式
     *  前端获取这个参数 在通过推荐注册的时候 用户提交表单需要带上这个参数
     *  注册处理逻辑要解密和校验这个referral
     * @return mixed
     */
    public function generateSpread(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        if ($member_id != '') {
            $query_params = [
                'referral' => Aes::letSecret($member_id, 'E', config('app.referral_token')),
            ];
            $redirect = config('app.fe_url').http_build_query($query_params);
            $imgObj = QrCode::size(300)
                ->color(0,0,0)
                // ->format('png')
                ->backgroundColor(255,255,255)
                ->margin(0)->generate($redirect);
            return $imgObj;
            // return base64_encode($imgObj);
        }
    }

	public function generateSpreadUrl(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $query_params = [
            'referral' => Aes::letSecret($member_id, 'E', config('app.referral_token')),
        ];
        return config('app.fe_url').http_build_query($query_params);
    }
    /**
     * 生成并返回收款二维码
     * 链接地址包括用户的id
     * @param receipt 服务会员的id 采用Aes的自定义加密方式
     *  前端获取这个参数 用户提交表单需要带上这个参数
     *  转账处理逻辑要解密和校验这个receipt
     * @return mixed
     */
    public function generateReceipt(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        //判断当前用户是否是服务会员
        $member = DB::table('user')->where('id',$member_id)->first();
        if(empty($member)){
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        if($member->member_level!=1 || empty($member->apply_progress)){
            return M3Result::init(ErrorCode::$SERVER_USER_INVAILD);
        }elseif($member->apply_progress==1){
            return M3Result::init(ErrorCode::$SERVER_USER_PEDDING);
        }elseif($member->apply_progress==3){
            return M3Result::init(ErrorCode::$SERVER_USER_REJECT);
        }
        $query_params = [
            'receipt' => Aes::letSecret($member_id, 'E', config('app.receipt_token')),
        ];
        $redirect = config('app.receipt_url').http_build_query($query_params);
        $imgObj = QrCode::size(300)
            ->color(0,0,0)
            // ->format('png')
            ->backgroundColor(255,255,255)
            ->margin(0)->generate($redirect);
        return M3Result::init(ErrorCode::$OK,$imgObj,$redirect);
        //return $imgObj;
        // return base64_encode($imgObj);
    }
}
