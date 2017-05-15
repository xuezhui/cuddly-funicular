<?php
namespace App\Http\Controllers\Api;

use App\Entity\Product;
use App\Http\Controllers\Controller;
use App\Models\ErrorCode;
use App\Models\M3Result;
use Illuminate\Http\Request;
use App\Entity\CartItem;
use Illuminate\Support\Facades\DB;
use Log;
/**
 * Author: yanpengcheng
 * DateTime: 2017/4/2 20:34
 * Description:
 * 购物车的新增修改和删除
 */
class CartController extends Controller
{
    /**
     * 添加商品到购物车
     * @param Request $request
     * @param $product_id
     * @return string
     */
    public function addCart(Request $request)
    {
        // 如果当前已经登录 则直接操作数据表购物车
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $cart_json = $request->input('cart_json', '');
        if ($cart_json == '') {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        $cart_arr = json_decode($cart_json, true);
        if (!$cart_arr) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        $keys = ['product_id', 'spec_id', 'count'];
        foreach ($keys as $value) {
            if (!array_key_exists($value, $cart_arr)) {
                return M3Result::init(ErrorCode::$PARAM_ERROR);
            }
        }
        $supplier_id = Product::where('id', $cart_arr['product_id'])->value('supplierid');
        if (!$supplier_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR, '供应商ID异常');
        }
        //购物车所有商品规格的id数组 $cart_arr必须为二维数组
        $cart_item = CartItem::where([
            'member_id' => $member_id,
            'product_id' => $cart_arr['product_id'],
            'spec_id' => $cart_arr['spec_id']
        ])->first();
        //新增
        if (is_null($cart_item)) {
            CartItem::insert([
                'member_id' => $member_id,
                'product_id' => $cart_arr['product_id'],//商品id（在商品表里唯一 可以代表某个供应商的商品）
                'spec_id' => $cart_arr['spec_id'],//规格id
                'supplierid' => $supplier_id,//供应商id
                'count' => $cart_arr['count'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            //增加数量
            CartItem::where([
                'member_id' => $member_id,
                'supplierid' => $supplier_id,
                'spec_id' => $cart_arr['spec_id']
            ])->increment('count', $cart_arr['count']);
        }
        return M3Result::init(ErrorCode::$OK);
    }

    /**
     * 编辑购物车
     * @param Request $request
     * @return string
     */
    public function editCart(Request $request)
    {
        // 如果当前已经登录 则直接操作数据表购物车
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $cart_json = $request->input('cart_json', '');
        if ($cart_json == '') {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        $cart_arr = json_decode($cart_json);//前端提交过来的购物车json解析为对象数组
        if (!$cart_arr) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        try {
            DB::beginTransaction();
            //购物车所有商品规格的id数组 $cart_arr必须为二维数组
            foreach ($cart_arr->value as $pdt) {
                $supplier_id = Product::where('id', $pdt->spec->product_id)->value('supplierid');
                if ($supplier_id != $cart_arr->supplier_id) {
                    return M3Result::init(ErrorCode::$PARAM_ERROR, '供应商ID异常');
                }
                //修改数量
                DB::table('cart_item')->where([
                    'member_id' => $member_id,
                    'supplierid' => $supplier_id,
                    'spec_id' => $pdt->spec->id
                ])->update(['count' => $pdt->count]);
            }
            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        } catch (\Exception $e) {
            Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
        }
        return M3Result::init(ErrorCode::$FAIL);
    }
    /**
     * 删除购物车中的商品
     * 删除分为：
     * 一个一个的删除
     * 某个商品的整个删除
     * 某几个商品的整个删除
     * @param Request $request
     * @return string
     */
    public function deleteCart(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        //用户登陆直接操作购物车数据表
        $product_id = $request->input('product_id', '');
        $spec_id = $request->input('spec_id', '');
        if ($product_id == '') {
            return M3Result::init(ErrorCode::$GOOD_ID_INVALID);
        }
        if ($spec_id == '') {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        $supplier_id = Product::where('id', $product_id)->value('supplierid');
        if (!$supplier_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR, '供应商ID异常');
        }
        $res = CartItem::where('member_id', $member_id)->where(['supplierid' => $supplier_id,'spec_id' => $spec_id])->delete();
        if ($res) {
            return M3Result::init(ErrorCode::$OK);
        }
        return M3Result::init(ErrorCode::$FAIL);
    }

    /**
     * 批量清除失效宝贝
     */
    public function purgeExpiredGoods(Request $request)
    {
        $member_id = $request->input('uid', 0);
        $cart_json = $request->input('cart_json', '');
        if (!$member_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if ($cart_json == '') {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        $cartObjects = json_decode($cart_json);
        if (!$cart_json) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        try {
            DB::beginTransaction();
            foreach ($cartObjects as $item) {
                DB::table('cart_item')->where(['supplierid' => $item->supplier_id, 'product_id' => $item->product_id, 'spec_id' => $item->spec_id])->delete();
            }
            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        } catch (\Exception $e) {
            Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
        }
        return M3Result::init(ErrorCode::$FAIL);
    }
    /**
     * 购物车商品列表
     * 购物车商品显示为 商铺名称 => 用户购物车里该商铺下的商品列表
     * @param Request $request
     */
    public function CartList(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $list_obj = CartItem::where('member_id', $member_id)->orderBy('created_at', 'desc')->paginate(20);
        //dd($list_obj);
        $supplier = [];
        foreach ($list_obj as $item) {
            $supplier[$item->supplierid]['supplier_id'] = $item->supplierid;
            $supplier[$item->supplierid]['supplier_name'] = DB::table('zh_supplier')->where('id', $item->supplierid)->value('suppliername');
            //DB类的get方法是数组对象 first是对象
            $product_spec = DB::table('pro_spec')->where('id', $item->spec_id)->first();
            if (is_object($product_spec)) {
                $supplier[$item->supplierid]['value'][] = [
                    'count' => $item->count,
                    'spec' => $product_spec,
                    'product' => Product::where('id', $product_spec->product_id)->first()
                ];
            }
        }
        $dataWithPage = $list_obj->toArray();
        $dataWithPage['data'] = array_values($supplier);
        return M3Result::init(ErrorCode::$OK, $dataWithPage);
    }
}
