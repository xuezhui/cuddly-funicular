<?php

namespace App\Http\Controllers\Admin;

use App\Models\ErrorCode;
use App\Models\M3Result;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Base;

class ConfigController extends BaseController
{
    /**
     * 充值额度配置
     */
    public function chargeAmount()
    {
        $config = config('allowAmount');
        return view('Admin.config.charge_amount', compact('config'));
    }

    /**
     * 添加的页面
     */
    public function amountAdd()
    {
        return view('Admin.config.amount_add');
    }

    /**
     * 执行添加
     * @param Request $request
     */
    public function amountPut(Request $request)
    {
        $config = config('allowAmount');
        if (!is_array($config)) {
            return M3Result::init(ErrorCode::$PARAM_ERROR, '获取额度配置失败');
        }
        if (count($config) <= 0) {
            return M3Result::init(ErrorCode::$PARAM_ERROR, '获取额度配置失败');
        }
        $amount = $request->amount;
        if (in_array($amount, $config)) {
            return M3Result::init(ErrorCode::$PARAM_ERROR, '额度已存在');
        }
        if (!ctype_digit($amount)) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if ($amount <= 0) {
            return M3Result::init(ErrorCode::$PARAM_ERROR, '额度不符合规范');
        }
        array_push($config, (int)$amount);
        sort($config);
        $configStr = "<?php\n return \n".var_export($config, true).';';
        Storage::disk('config')->put('allowAmount.php', $configStr);
        $configNew = config('allowAmount');
        if (is_array($configNew) && count($configNew) >0) {
            return M3Result::init(ErrorCode::$OK);
        }
        return M3Result::init(ErrorCode::$FAIL);
    }
    /**
     * 执行删除
     * @param Request $request
     */
    public function amountDel(Request $request)
    {
        $config = config('allowAmount');
        if (!is_array($config)) {
            return M3Result::init(ErrorCode::$PARAM_ERROR, '获取额度配置失败');
        }
        if (count($config) <= 0) {
            return M3Result::init(ErrorCode::$PARAM_ERROR, '获取额度配置失败');
        }
        $amount = $request->amount;
        if (!in_array($amount, $config)) {
            return M3Result::init(ErrorCode::$PARAM_ERROR, '额度不存在');
        }
        if (!ctype_digit($amount)) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if ($amount <= 0) {
            return M3Result::init(ErrorCode::$PARAM_ERROR, '额度不符合规范');
        }
        $key = array_search((int)$amount, $config);
        unset($config[$key]);
        sort($config);
        $configStr = "<?php\n return \n".var_export($config, true).';';
        Storage::disk('config')->put('allowAmount.php', $configStr);
        $configNew = config('allowAmount');
        if (is_array($configNew) && count($configNew) >0) {
            return M3Result::init(ErrorCode::$OK);
        }
        return M3Result::init(ErrorCode::$FAIL);
    }
}
