<?php
/**
 * Created by PhpStorm.
 * User: NYJ
 * Date: 2017/3/29
 * Time: 15:34
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\ErrorCode;
use App\Models\M3Result;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShopAddressController extends Controller
{
    public function update(Request $request)
    {
        $nShopAddressID = $request->input('id');
        if (is_null($nShopAddressID))
        {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }

        $addressModel = DB::table('shop_address')->where('id',$nShopAddressID)->first();

        if (is_null($addressModel))
        {
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }

        $query = DB::table('shop_address')->where('id',$nShopAddressID);
        $_tmp = [];
        if (!is_null($nIsDefault = $request->input('is_default')))
        {
            //只允许一个default地址
            if ($nIsDefault == 1)
            {
               DB::table('shop_address')->where(['user_id' => $addressModel->user_id,  'is_default' => 1])->update(['is_default' => 0]);
            }

            $_tmp['is_default'] = $nIsDefault;
        }

        if ($strUsername = $request->input('username'))
        {
            $_tmp['username'] = $strUsername;
        }

        if ($strUserPhone = $request->input('user_phone'))
        {
            $_tmp['user_phone'] = $strUserPhone;
        }

        if ($strUserAddress = $request->input('user_address'))
        {
            $_tmp['user_address'] = $strUserAddress;
        }

        if ($nUserAreaId = $request->input('user_area_id'))
        {
            $_tmp['user_area_id'] = $nUserAreaId;
        }

        if (empty($_tmp))
        {
            return M3Result::init(ErrorCode::$NO_CHANGE_FOUND);
        }

        try {
            $re = $query->update($_tmp);
        } catch (Exception $error) {
            return M3Result::init(ErrorCode::$DB_ERROR,$error);
        }

        if ($re)
        {
            $addressModel = DB::table('shop_address')->where('id',$nShopAddressID)->first();
            return M3Result::init(ErrorCode::$OK,$addressModel);
        }
        return M3Result::init(ErrorCode::$NO_CHANGE_FOUND);

    }

    /**
     * 收货地址软删除 防止订单详情那里查询不到
     * @param Request $request
     * @return string
     */
    public function delete(Request $request)
    {
        $nAddressId = $request->input('id');
        $re_delete = DB::table('shop_address')->where(['id' => $nAddressId])->update(['status' => 0]);
        if ($re_delete) {
            return M3Result::init(ErrorCode::$OK);
        }
        return M3Result::init(ErrorCode::$DB_ERROR);
    }


    public function getShopAddressList(Request $request)
    {
        $nUserId = $request->input('uid');
        $query = DB::table('shop_address');
        if (is_null($nUserId) || $nUserId == 0) {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $query = $query->where(['user_id' => $nUserId, 'status' => 1]);
        $addressList = $query->paginate(10);
        return M3Result::init(ErrorCode::$OK,$addressList);
    }

    /**
     * 添加收货地址
     * @param Request $request
     * @return string
     */
    public function add(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $validate = Validator::make($request->all(),
            [
                'username'     => 'required|string',
                'user_phone'   => 'required|string',
                'user_address' => 'required|string',
            ]);

        if ($validate->fails()) {
            return M3Result::init(ErrorCode::$PARAM_ERROR,$validate->errors()->first());
        }
        $chechExist = DB::table('user')->where('id', $member_id)->first();
        if (is_null($chechExist)) {
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }

        //只允许一个default地址
        $nIsDefault = $request->input('is_default', 0);
        if ($nIsDefault == 1) {
            DB::table('shop_address')->where(['user_id' => $member_id,  'is_default' => 1])->update(['is_default' => 0]);
        }

        $newAddressId = DB::table('shop_address')->insertGetId(
        [
            'user_id'      => $member_id,
            'username'     => $request->input('username'),
            'user_phone'   => $request->input('user_phone'),
            'user_address' => $request->input('user_address'),
            'is_default'   => $nIsDefault,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $newAddress = DB::table('shop_address')->where('id', $newAddressId)->first();
        return M3Result::init(ErrorCode::$OK, $newAddress);
    }

    /**
     * 获取用户收货地址详情
     */
    public function detail(Request $request)
    {
        $member_id = $request->input('uid', 0);//用户ID
        $address_id = $request->input('address_id', 0);
        if (!$member_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if (!$address_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        $detail = DB::table('shop_address')->where(['id' => $address_id, 'user_id' => $member_id])->first();
        return M3Result::init(ErrorCode::$OK, $detail);
    }
    /**
     * 判断目标key是否都存在，多个key可用逗号隔开或组成数组
     * @param  string|array $p_keys 多个key可用逗号隔开的字符串或组成数组
     * @param  bool         $p_allowBlank 是否允许空值
     * @return bool         true/false
     */
    public static function issetRequest($p_keys, $p_allowBlank=false)
    {
        return is_null(static::getUnsetRequest($p_keys,$p_allowBlank));
    }
    /**
     * 判断目标key是否都存在，返回首个不存在的key
     * @param  string|array $p_keys       多个key可用逗号隔开的字符串或组成数组
     * @param  bool         $p_allowBlank 是否允许空值
     * @return bool         true/false
     */
    public static function getUnsetRequest($p_keys,$p_allowBlank=false)
    {
        $p_keys = explode(',', $p_keys);
        $unsetKey = null;
        foreach ($p_keys as $p_key)
        {
            if (!array_key_exists($p_key, $_REQUEST) || (!$p_allowBlank && $_REQUEST[$p_key]==null ))
            {
                $unsetKey = $p_key;
                break;
            }
        }
        return $unsetKey;
    }
}