<?php

namespace App\Http\Controllers\Service;

use App\Entity\TempPhone;
use App\Models\ErrorCode;
use App\Models\M3Result;
use App\Tool\Validate\Verify;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    public function sendSMS(Request $request)
    {
        $phone = $request->input('telephone', '');
        //手机号码不合法
        if (!Verify::isPhone($phone)) {
            return M3Result::init(ErrorCode::$TELEPHONE_INVALID);
        }
        $code = '';
        $charset = '1234567890';
        $_len = strlen($charset) - 1;

        for ($i = 0;$i < 6; ++$i) {
            $code .= $charset[mt_rand(0, $_len)];
        }
        //签名必须写在头部 可在后台配置签名
        $datas = [
            'mobile' => $phone,
            'text' => '【xxx】您的验证码是'.$code
        ];

        require_once(app_path() . "/Tool/yunpiansms/YunpianAutoload.php");
        $smsOperator = new \SmsOperator();
        $result = $smsOperator->single_send($datas);

        if ($result->success) {
            $tempPhone = TempPhone::where('telephone', $phone)->first();
            if($tempPhone == null) {
                $tempPhone = new TempPhone;
            }
            $tempPhone->telephone = $phone;
            $tempPhone->verify_code = $code;
            $tempPhone->deadline = date('Y-m-d H:i:s', time() + 60 * 60);
            $tempPhone->save();
            return M3Result::init(ErrorCode::$OK);
        } else {
            //dd($result);
            if (!$result->success && $result->statusCode == 400) {
                return M3Result::init(ErrorCode::$USER_SMS_TEL_TOO_MORE, '', $result->responseData['detail']);
            }
            Log::error($result->responseData);
            return M3Result::init(ErrorCode::$SEND_SMS_ERROR);
        }
    }
}
