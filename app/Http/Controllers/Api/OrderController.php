<?php

namespace App\Http\Controllers\Api;
use App\Entity\Product;
use App\Http\Controllers\Controller;
use App\Models\ErrorCode;
use App\Models\M3Result;
use App\Tool\Helper;
use App\Tool\Validate\Aes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Log;
use Ramsey\Uuid\Uuid;
use App\Http\Controllers\Service\ExpressController;
/**
 * Author: yanpengcheng
 * DateTime: 2017/4/2 20:34
 * Description:
 * 订单生成、结算
 */
class OrderController extends Controller
{
    /**
     * 提交并生成订单
     * 提交订单分为直接购买和购物车结算后提交
     * @param Request $request
     * @return $this
     */
    public function toOrderCommit(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        //订单json里存每个商品的id 规格id
        $order_json = $request->input('order_json', '');
        $orderObjects = json_decode($order_json);//array
        if (!$orderObjects) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        //dd($orderObjects);
        $shop_address_id = $request->input('shop_address_id', '');//收货地址ID
        $distribution_way = $request->input('distribution_way', 1);//配送方式
        $buy_path = $request->input('buy_path', 1);//1-直接购买 2-购物车结算
        $total_price = 0;//总订单总价
        $each_amount = [];//存储各子订单订单价格
        $return = [];//子订单
        if ($shop_address_id == '') {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        try {
            DB::beginTransaction();
            //先保存用户ID到订单表 用于生成订单ID
            $order_id = DB::table('zh_order')->insertGetId([
                'customer_id' => $member_id,
                'shop_address_id' => $shop_address_id,//收货地址ID
                'buy_path' => $buy_path,
                'created_at' => date('Y-m-d H:i:s', time()),
                'updated_at' => date('Y-m-d H:i:s', time())
            ]);
            $order_no = date('Ymd').$member_id.$order_id.substr(Uuid::uuid1()->getInteger(),0,10);//总订单号
            //@TODO 防止价格等被篡改掉
            //@TODO 分组付款
            foreach ($orderObjects as $values) {
                $each_amount[$values->supplier_id]['each_price'] = 0;
                foreach ($values->value as $cart_item) {
                    //从产品表取得产品的信息比如价格 然后赋值给一个对象属性
                    $product_spec = DB::table('pro_spec')->where('id', $cart_item->spec_id)->first();
                    if (!is_object($product_spec)) {
                        return M3Result::init(ErrorCode::$PARAM_ERROR, '产品规格数据异常');
                    }
                    //检验供应商ID防止篡改
                    $supplier_id_chek = Product::where('id', $product_spec->product_id)->value('supplierid');
                    if ($supplier_id_chek != $values->supplier_id) {
                        return M3Result::init(ErrorCode::$PARAM_ERROR, '商户ID数据异常');
                    }
                    //判断每种规格商品库存 如果有库存不足的则阻止订单生成
                    if ($product_spec->stock < $cart_item->count) {
                        return M3Result::init(ErrorCode::$STOCK_INSUFFICIENT, $product_spec);
                    }
                    //商品价格具体到某规格商品的价格 从表里去查为了防止客户端篡改掉价格
                    //商品数量是前端提交过来的
                    $each_amount[$values->supplier_id]['each_price'] += $product_spec->cur_price * $cart_item->count;
                    //实现订单快照
                    DB::table('pro_order_item')->insert([
                        'order_id' => $order_id,//订单ID
                        'pro_id' => $cart_item->product_id,//产品ID
                        'spec_id' => $cart_item->spec_id,//产品规格ID
                        'pro_num' => $cart_item->count,//产品数量
                        'supplierid' => $values->supplier_id,//供应商ID
                        'pro_count_price' => $product_spec->cur_price * $cart_item->count//商品总价
                    ]);

                    //如果不是直接购买 则生成订单后删除购物车中的商品
                    if ($buy_path == 2) {
                        DB::table('cart_item')->where('member_id', $member_id)->where(['product_id' => $cart_item->product_id,'spec_id' => $cart_item->spec_id])->delete();
                    }
                    //判断库存是否够减
                    if ($product_spec->stock >= $cart_item->count) {
                        //规格表里库存减少
                        DB::table('pro_spec')->where('id', $cart_item->spec_id)->decrement('stock', $cart_item->count);
                        //产品表里总库存也减少
                        DB::table('product')->where('id', $cart_item->product_id)->decrement('stock', $cart_item->count);
                    }
                }
                //生成子订单
                DB::table('zh_order_item')->insert([
                    'order_id' => $order_id,//订单ID
                    'supplierid' => $values->supplier_id,//供应商ID
                    'childno' => date('Ymd').$member_id.$order_id.substr(Uuid::uuid1()->getInteger(),0,10),//子订单号
                    'member_id' => $member_id,//用户ID
                    'distribution_way' => $distribution_way,//配送方式
                    'payment_status' => 0,//订单支付状态0:未支付  1:已支付 2:待续、、
                    'logistics_status' => 0,//物流状态 0-待发货 1-待收货 2-已确认收货
                    'total_amount' => $each_amount[$values->supplier_id]['each_price'],
                    'created_at' => date('Y-m-d H:i:s', time())
                ]);
            }
            //计算总订单总价 并构造返回各子订单总价的数组
            //用于后续订单支付
            foreach ($each_amount as $supplier => $item) {
                $total_price += $item['each_price'];
                $return[] = [
                    'supplier_id' => $supplier,
                    'supplier_token' => Aes::letSecret($supplier, 'E', config('app.supplier_token')),
                    'each_fee_str' => Aes::letSecret($item['each_price'], 'E', config('app.sub_order_token')),
                    'each_price' => $item['each_price']
                ];
            }
            //生成总订单
            $orderArr = [
                'no' => $order_no,//总订单号
                'total_amount' => Helper::formatMoney($total_price),//订单总价
                'updated_at' => date('Y-m-d H:i:s', time())
            ];
            DB::table('zh_order')->where('id', $order_id)->update($orderArr);
            $orderArr['order_id'] = $order_id;//返回订单ID
            //订单总价加密字符串用于后台校验 防止用户前端篡改
            $orderArr['order_fee_str'] = Aes::letSecret($orderArr['total_amount'], 'E', config('app.order_token'));
            $orderArr['sub_order'] = $return;
            DB::commit();
            return M3Result::init(ErrorCode::$OK, $orderArr);
        } catch (\Exception $e) {
            Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
        }
        return M3Result::init(ErrorCode::$FAIL);
    }


    /**
     * 用户的所有订单列表
     * @param Request $request
     * @return $this
     */
    public function toOrderList(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        $logistics_status = $request->input('logistics_status', '');//物流状态
        $payment_status = $request->input('payment_status', '');//支付状态
        $query = DB::table('zh_order_item')->where(['member_id' => $member_id, 'status' => 1]);
        if ($logistics_status != '') {
            $valid = [0, 1, 2];
            $logistics = explode(',', $logistics_status);
            foreach ($logistics as $val) {
                if (!in_array($val, $valid)) {
                    $logistics = [0, 1];
                    break;
                }
            }
            $query = $query->whereIn('logistics_status', $logistics);
        }
        if ($payment_status != '') {
            $query = $query->where('payment_status', $payment_status);
        }
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        //分组重复的供应商
        foreach ($orders as $order) {
            if (isset($order->id)) {
                $order->address_id = DB::table('zh_order')->where(['customer_id' => $member_id, 'id' => $order->order_id])->value('shop_address_id');
                //总订单金额
                //$overall_total_amount = DB::table('zh_order')->where(['customer_id' => $member_id, 'id' => $order->order_id])->value('total_amount');
                //$order->order_fee_str = Aes::letSecret($overall_total_amount, 'E', config('app.order_token'));
                $order->supplier_token = Aes::letSecret($order->supplierid, 'E', config('app.supplier_token'));
                $order->supplier_name = DB::table('zh_supplier')->where('id', $order->supplierid)->value('suppliername');
                $order->each_fee_str = Aes::letSecret($order->total_amount, 'E', config('app.sub_order_token'));
                //子订单 用户购买单个供应商的商品详细
                $product_items = DB::table('pro_order_item')->where(['order_id' => $order->order_id, 'supplierid' => $order->supplierid])->get();
                $order->order_items = $product_items;
                foreach ($product_items as $product_item) {
                    if (is_object($product_item)) {
                        $pdt = DB::table('product')->where(['id' => $product_item->pro_id, 'supplierid' => $product_item->supplierid])->first();
                        if (is_null($pdt)) {
                            return M3Result::init(ErrorCode::$INCOMPLETE_DATA, 'product');
                        }
                        $spec = DB::table('pro_spec')->where('id', $product_item->spec_id)->first();
                        if (is_null($spec)) {
                            return M3Result::init(ErrorCode::$INCOMPLETE_DATA, 'spec');
                        }
                        $product_item->max_price = DB::table('pro_spec')->where('product_id', $product_item->pro_id)->max('cur_price');
                        $product_item->min_price  = DB::table('pro_spec')->where('product_id', $product_item->pro_id)->min('cur_price');
                        $product_item->product_name = $pdt->name;
                        $product_item->photos = $pdt->photos;
                        $product_item->remarks = $pdt->remarks;
                        $product_item->description = $pdt->description;

                        $product_item->spec_name = $spec->name;
                        $product_item->spec_stock = $spec->stock;
                        $product_item->cur_price = $spec->cur_price;
                        $product_item->market_price = $spec->market_price;
                    }
                }
            }
        }
        return M3Result::init(ErrorCode::$OK, $orders->toArray());
    }

    /**
     * 使用购物券支付订单
     * 订单前台显示总订单支付
     * 后台处理是按照供应商子订单支付
     * 如果账户里的购物券不足 引导用户充值
     * 应用场景：多供应商支付
     */
    public function toOrderPay(Request $request)
    {
        $order_id = $request->input('order_id', '');//总订单ID
        $order_fee_str = $request->input('order_fee_str', '');//总订单校验码
        $total_amount = $request->input('total_amount', '');//订单总额
        $payment_method = $request->input('payment_method', '');//支付方式
        $member_id = $request->input('uid', '');//用户ID
        $pay_password = $request->input('pay_password', '');//支付密码
        $sub_order_json = $request->input('sub_order_json', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        if ($order_id == '') {
            return M3Result::init(ErrorCode::$ORDER_ID_INVALID);
        }
        if ($sub_order_json == '') {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if (!$total_amount) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if (strlen($pay_password) < 6) {
            return M3Result::init(ErrorCode::$PAY_PASSWORD_INVALID);
        }
        $sub_orders = json_decode($sub_order_json);//对象数组
        if (!$sub_orders) {
            return M3Result::init(ErrorCode::$PARAM_ERROR, 'sub order json is invalid');
        }
        $token = Aes::letSecret($order_fee_str, 'D', config('app.order_token'));
        if ($token != $total_amount) {
            Log::error('用户=' . $member_id . '总订单金额应为='.$token.' 篡改为='.$total_amount.'总订单ID='.$order_id);
            return M3Result::init(ErrorCode::$PARAM_ERROR, '总订单金额异常');
        }
        //校验用户剩余购物券
        $coupon_total = DB::table('fund_pool')->where('member_id', $member_id)->value('coupon_total');
        //余额不足
        if ($total_amount > $coupon_total) {
            return M3Result::init(ErrorCode::$MONEY_INSUFFICIENT);
        }
        //校验支付密码
        $user_pay_password = DB::table('user')->where('id', $member_id)->value('pay_password');
        if (!Hash::check($pay_password, $user_pay_password)) {
            return M3Result::init(ErrorCode::$PAY_PASSWORD_INVALID);
        }
        try { 
            DB::beginTransaction();
            //扣除用户订单金额
            DB::table('fund_pool')->where('member_id', $member_id)->decrement('coupon_total', $total_amount);
            //更新总订单支付方式
            DB::table('zh_order')->where('id', $order_id)->update([
                'payment_method' => $payment_method//支付方式
            ]);
            foreach ($sub_orders as $order_item) {
                //检验每个商户(供应商)订单结算额度
                $subToken = Aes::letSecret($order_item->each_fee_str, 'D', config('app.sub_order_token'));
                if ($subToken != $order_item->each_price) {
                    Log::error('用户=' . $member_id . '子订单金额应为='.$subToken.' 篡改为='.$order_item->each_price.'总订单ID='.$order_id);
                    return M3Result::init(ErrorCode::$PARAM_ERROR, '子订单金额异常');
                }
                //校验供应商id是否被前端篡改
                $decrypt_supplier = Aes::letSecret($order_item->supplier_token, 'D', config('app.supplier_token'));
                if ($decrypt_supplier != $order_item->supplier_id) {
                    Log::error('用户=' . $member_id . '支付时供应商ID='.$decrypt_supplier.' 篡改为='.$order_item->supplier_id.'总订单ID='.$order_id);
                    return M3Result::init(ErrorCode::$PARAM_ERROR, '支付时供应商ID异常');
                }
                //更新子订单支付 物流状态
                DB::table('zh_order_item')
                    ->where(['order_id' => $order_id, 'member_id' => $member_id, 'supplierid' => $order_item->supplier_id])
                    ->update([
                        'payment_status' => 1,//订单支付状态0:未支付  1:已支付 2:待续
                        'logistics_status' => 0, //物流状态 0-待发货 1-待收货 2-已确认收货
                        'pay_at' => date('Y-m-d H:i:s', time()),//支付时间
                        'updated_at' => date('Y-m-d H:i:s', time()),
                    ]);
                //针对每个供应商写入流水日志
                DB::table('capitallog')->insert([
                    'user_id' => $order_item->supplier_id,
                    'rebate_user_id' => $member_id,
                    'capital_type' => 10,//用户消费返给供应商积分
                    'amount' => Helper::formatMoney($order_item->each_price),
                    'order_id' => $order_id,
                    'note' => '用户ID=' . $member_id.'在供应商ID=' .
                        $order_item->supplier_id.'消费amount='.$order_item->each_price.'总订单ID='.$order_id,//最真实的记录到备注 其余都是舍去法保留两位
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            //用户消费日志
            DB::table('capitallog')->insert([
                'user_id'        => $member_id,
                'capital_type'   => 2,//消费
                'amount'         => Helper::formatMoney($total_amount),
                'order_id' => $order_id,
                'note'           => '购物券消费'.$total_amount.'总订单ID='.$order_id,//最真实的记录到备注 其余都是舍去法保留两位
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        } catch (\Exception $e) {
            Log::error('用户支付时购物券扣除失败:用户ID='.$member_id.'应扣除='.$total_amount);
            Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
        }
        return M3Result::init(ErrorCode::$FAIL);
    }

    /**
     * 使用购物券支付订单
     * 订单前台显示总订单支付
     * 后台处理是按照供应商子订单支付
     * 如果账户里的购物券不足 引导用户充值
     * 应用场景：单个供应商支付
     */
    public function toOrderSinglePay(Request $request)
    {
        $order_id = $request->input('order_id', '');//子订单ID
        $each_fee_str = $request->input('each_fee_str', '');//订单校验码
        $total_amount = $request->input('total_amount', '');//订单总额
        $payment_method = $request->input('payment_method', '');//支付方式
        $member_id = $request->input('uid', '');//用户ID
        $pay_password = $request->input('pay_password', '');//支付密码
        $supplier_token = $request->input('supplier_token', '');//供应商校验码
        $supplier_id = $request->input('supplier_id', 0);//供应商ID
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        if ($order_id == '') {
            return M3Result::init(ErrorCode::$ORDER_ID_INVALID);
        }
        if (!$total_amount) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if (strlen($pay_password) < 6) {
            return M3Result::init(ErrorCode::$PAY_PASSWORD_INVALID);
        }
        $token = Aes::letSecret($each_fee_str, 'D', config('app.sub_order_token'));
        if ($token != $total_amount) {
            Log::error('用户=' . $member_id . '订单金额应为='.$token.' 篡改为='.$total_amount.'订单ID='.$order_id);
            return M3Result::init(ErrorCode::$PARAM_ERROR, '订单金额异常');
        }
        //校验供应商id是否被前端篡改
        $decrypt_supplier = Aes::letSecret($supplier_token, 'D', config('app.supplier_token'));
        if ($decrypt_supplier != $supplier_id) {
            Log::error('用户=' . $member_id . '支付时供应商ID='.$decrypt_supplier.' 篡改为='.$supplier_id.'订单ID='.$order_id);
            return M3Result::init(ErrorCode::$PARAM_ERROR, '支付时供应商ID异常');
        }
        //校验用户剩余购物券
        $coupon_total = DB::table('fund_pool')->where('member_id', $member_id)->value('coupon_total');
        //余额不足
        if ($total_amount > $coupon_total) {
            return M3Result::init(ErrorCode::$MONEY_INSUFFICIENT);
        }
        //校验支付密码
        $user_pay_password = DB::table('user')->where('id', $member_id)->value('pay_password');
        if (!Hash::check($pay_password, $user_pay_password)) {
            return M3Result::init(ErrorCode::$PAY_PASSWORD_INVALID);
        }
        $payment_status = DB::table('zh_order_item')
            ->where(['id' => $order_id, 'member_id' => $member_id, 'supplierid' => $supplier_id])
            ->value('payment_status');
        if ($payment_status == 1) {
            return M3Result::init(ErrorCode::$PARAM_ERROR, '该订单已支付');
        }
        try {
            DB::beginTransaction();
            //扣除用户订单金额
            DB::table('fund_pool')->where('member_id', $member_id)->decrement('coupon_total', $total_amount);
            //更新总订单支付方式
            $overall_order_id = DB::table('zh_order_item')
                ->where(['id' => $order_id, 'member_id' => $member_id, 'supplierid' => $supplier_id])
                ->value('order_id');//总订单ID
            DB::table('zh_order')->where('id', $overall_order_id)->update([
                'payment_method' => $payment_method//支付方式
            ]);
            //更新子订单支付 物流状态
            DB::table('zh_order_item')
                ->where(['id' => $order_id, 'member_id' => $member_id, 'supplierid' => $supplier_id])
                ->update([
                    'payment_status' => 1,//订单支付状态0:未支付  1:已支付 2:待续
                    'logistics_status' => 0, //物流状态 0-待发货 1-待收货 2-已确认收货
                    'pay_at' => date('Y-m-d H:i:s', time()),//支付时间
                    'updated_at' => date('Y-m-d H:i:s', time()),
                ]);
            //针对每个供应商写入流水日志
            DB::table('capitallog')->insert([
                'user_id' => $supplier_id,
                'rebate_user_id' => $member_id,
                'capital_type' => 10,//用户消费返给供应商积分
                'amount' => Helper::formatMoney($total_amount),
                'order_id' => $order_id,
                'note' => '用户ID=' . $member_id.'在供应商ID=' .
                    $supplier_id.'消费amount='.$total_amount.'子订单ID='.$order_id,//最真实的记录到备注 其余都是舍去法保留两位
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            //用户消费日志
            DB::table('capitallog')->insert([
                'user_id'        => $member_id,
                'capital_type'   => 2,//消费
                'amount'         => Helper::formatMoney($total_amount),
                'order_id' => $order_id,
                'note'           => '购物券消费'.$total_amount.'子订单ID='.$order_id,//最真实的记录到备注 其余都是舍去法保留两位
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        } catch (\Exception $e) {
            Log::error('用户支付时购物券扣除失败:用户ID='.$member_id.'应扣除='.$total_amount);
            Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
        }
        return M3Result::init(ErrorCode::$FAIL);
    }
    /**
     * 确认收货
     * 货款打入账户
     */
    public function confirmReceipt(Request $request)
    {
        $order_id = $request->input('order_id', 0);//总订单ID
        $sub_order_id = $request->input('sub_order_id', 0);//子订单ID
        $supplier_id = $request->input('supplier_id', 0);//供应商id
        $member_id = $request->input('uid', 0);
        if (!$member_id) {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        if (!$order_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if (!$sub_order_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if (!$supplier_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        $where = [
            'id' => $sub_order_id,
            'order_id' => $order_id,
            'member_id' => $member_id,
            'supplierid' => $supplier_id
        ];
        try {
            DB::beginTransaction();
            DB::table('zh_order_item')->where($where)->update([
                'logistics_status' => 2,
                'finished_at' => date('Y-m-d H:i:s', time()),//成交时间
                'updated_at' => date('Y-m-d H:i:s', time()),
            ]);
            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        } catch (\Exception $e) {
            Log::error('用户确认收货失败:用户ID='.$member_id.'参数='.var_export($where, true));
            Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
        }
        return M3Result::init(ErrorCode::$FAIL);
    }

    /**
     * 取消订单
     * 释放库存
     */
    public function cancelOrder(Request $request)
    {
        $order_id = $request->input('order_id', 0);
        $member_id = $request->input('uid', '');
        $sub_order_id = $request->input('sub_order_id', 0);//子订单ID
        $supplier_id = $request->input('supplier_id', 0);//供应商id
        if (!$order_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if (!$member_id) {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        if (!$sub_order_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if (!$supplier_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        $where = [
            'id' => $sub_order_id,
            'order_id' => $order_id,
            'member_id' => $member_id,
            'supplierid' => $supplier_id
        ];
        try {
            DB::beginTransaction();
            //删掉子订单
            DB::table('zh_order_item')->where($where)->delete();
            //如果子订单为0，则删除父订单
            $count = DB::table('zh_order_item')->where(['order_id' => $order_id, 'member_id' => $member_id])->count();
            if (empty($count)) {
                DB::table('zh_order')->where(['id' => $order_id, 'customer_id' => $member_id])->delete();
            }
            //释放库存
            $items = DB::table('pro_order_item')->where('order_id', $order_id)->get();
            if ($items) {
                foreach ($items as $item) {
                    //规格表里库存释放
                    DB::table('pro_spec')->where('id', $item->spec_id)->increment('stock', $item->pro_num);
                    //产品表里总库存也释放
                    DB::table('product')->where('id', $item->id)->increment('stock', $item->pro_num);
                }
            }
            //删掉订单条目
            DB::table('pro_order_item')->where(['order_id' => $order_id, 'supplierid' => $supplier_id])->delete();
            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        } catch (\Exception $e) {
            Log::error('用户订单取消失败 用户ID='.$member_id.'总订单ID='.$order_id.' 取消时子订单ID='.$sub_order_id.'供应商ID='.$supplier_id);
            Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
        }
        return M3Result::init(ErrorCode::$FAIL);
    }

    /**
     * 删除订单
     * 更改status为0
     */
    public function deleteOrder(Request $request)
    {
        $order_id = $request->input('order_id', '');//总订单ID
        $member_id = $request->input('uid', '');
        $sub_order_id = $request->input('sub_order_id', 0);//子订单ID
        $supplier_id = $request->input('supplier_id', 0);//供应商id
        if (!$order_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if (!$member_id) {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        if (!$sub_order_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if (!$supplier_id) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        $where = [
            'id' => $sub_order_id,
            'order_id' => $order_id,
            'member_id' => $member_id,
            'supplierid' => $supplier_id
        ];
        $res = DB::table('zh_order_item')->where($where)->update([
            'status' => 0,
            'updated_at' => date('Y-m-d H:i:s', time()),
        ]);
        if ($res) {
            return M3Result::init(ErrorCode::$OK);
        }
        return M3Result::init(ErrorCode::$FAIL);
    }

    /**
     * 用户订单和物流状态
     * @param Request $request
     */
    public function orderStatus(Request $request)
    {
        $member_id = $request->input('uid', '');
        if ($member_id == '') {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }
        //待支付
        $for_payment_count = DB::table('zh_order_item')->where(['member_id' => $member_id, 'status' => 1])->where('payment_status', 0)->count();
        //待收货
        $for_take_count = DB::table('zh_order_item')->where(['member_id' => $member_id, 'status' => 1])->where('logistics_status', 1)->count();
        return M3Result::init(ErrorCode::$OK, [
            'for_payment_count' => $for_payment_count,
            'for_take_count' => $for_take_count
        ]);
    }

    /**
     * 使用购物券线下支付（转账）
     * 如果账户里的购物券不足 引导用户充值
     */
    public function transfer(Request $request){
        $total_amount = $request->input('total_amount', '');//订单总额
        $member_id = $request->input('uid', '');
        $pay_password = $request->input('pay_password', '');
        $receipt = $request->input('receipt', '');
        if (empty($member_id)) {
            return M3Result::init(ErrorCode::$INVALID_USER_ID);
        }

        if (empty($total_amount) || $total_amount<=0) {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }
        if (strlen($pay_password) < 6) {
            return M3Result::init(ErrorCode::$PAY_PASSWORD_INVALID);
        }
        //校验用户剩余购物券
        $coupon_total = DB::table('fund_pool')->where('member_id', $member_id)->value('coupon_total');
        //dd($coupon_total);
        //余额不足
        if ($total_amount > $coupon_total) {
            return M3Result::init(ErrorCode::$MONEY_INSUFFICIENT);
        }
        //校验支付密码
        $user_pay_password = DB::table('user')->where('id', $member_id)->value('pay_password');
        if (!Hash::check($pay_password, $user_pay_password)) {
            return M3Result::init(ErrorCode::$PAY_PASSWORD_INVALID);
        }
        //校验积分增加方的身份
        $receipt_id = Aes::letSecret($receipt, 'D', config('app.receipt_token'));
        $receipt_member = DB::table('user')->where('id',$receipt_id)->first();
        if(!$receipt_member||$receipt_member->apply_progress!=2 || $receipt_member->member_level!=1){
            return M3Result::init(ErrorCode::$SERVER_USER_INVAILD);
        }
        try {
            DB::beginTransaction();
            //扣除用户订单金额
            $res = DB::table('fund_pool')->where('member_id', $member_id)->decrement('coupon_total', $total_amount);
            //增加服务会员积分
            $record = DB::table('fund_pool')->where('member_id',$receipt_id)->first();
            if(!$record){
                $fund_pool_record['member_id'] = $receipt_id;
                $fund_pool_record['srv_gold'] = $total_amount;
                $fund_pool_record['created_at'] = date('Y-m-d H:i:s');
                $fund_pool_record['updated_at'] = date('Y-m-d H:i:s');
                $fund_pool_record['deal_count'] = 1;
                DB::table('fund_pool')->insert($fund_pool_record);
            }else{
                DB::table('fund_pool')->where('member_id', $receipt_id)->update(['srv_gold'=>DB::raw('srv_gold+'.$total_amount),'deal_count'=>DB::raw('deal_count+1')]);
            }
            //写入消费者流水日志
            DB::table('capitallog')->insert([
                'user_id'        => $member_id,
                'capital_type'   => 2,//消费
                'amount'         => $total_amount,
                'note'           => '于店铺'.$receipt_member->store_name.' id='.$receipt_id.' 消费购物券'.$total_amount,//最真实的记录到备注 其余都是舍去法保留两位
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            //写入服务会员资金流水
            DB::table('capitallog')->insert([
                'user_id'        => $receipt_id,
                'rebate_user_id' => $member_id,
                'capital_type'   => 9,//积分
                'amount'         => $total_amount,
                'note'           => '用户id='.$member_id.' 在店铺消费'.$total_amount.'，增加相应积分'.$total_amount,//最真实的记录到备注 其余都是舍去法保留两位
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            DB::commit();
            return M3Result::init(ErrorCode::$OK);

        } catch (\Exception $e) {
            \Log::error('用户支付时购物券扣除失败:用户ID='.$member_id.'应扣除='.$total_amount);
            \Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
            DB::rollBack();
            return M3Result::init(ErrorCode::$DB_ERROR);
        }
    }

    //物流接口
    public function getExpress(Request $request){
        $member_id = $request->input('uid',0);
        $order_id = $request->input('orderid',0);
        $order = DB::table('zh_order_item')->where('member_id',$member_id)->where('id',$order_id)->first();
        if(empty($order)){
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }
        $express = ExpressController::poll(array('com'=>$order->logistics_type,'num'=>$order->logistics_number));
        $express = array_reverse(json_decode($express,true));
        //商品数
        $result['count'] = DB::table('pro_order_item')->where('order_id',$order_id)->count();
        //第一个商品
        $pro = DB::table('pro_order_item')->select('pro_id')->where('order_id',$order_id)->first();
        $result['img'] = "";
        if(!empty($pro)){
            $proi = DB::table('product')->where('id',$pro->pro_id)->first();
            $result['img'] = $proi->photos;
        }
        
        $expresscom = config('expresscom');
        if($order->logistics_type){
            $result['com'] = $expresscom[$order->logistics_type];
            $result['num'] = $order->logistics_number;
        }
        $states = ['0'=>'运输中','1'=>'揽件中','2'=>'疑难件','3'=>'已签收','4'=>'已退签','5'=>'派件中','6'=>'退回中'];
        if(empty($express['state'])){
            $express['state'] = 1;
        }
        $result['status'] = $states[$express['state']];

        if(strtolower($express['message'])!='ok'){
           return M3Result::init(ErrorCode::$EMPTY_EXPRESS_DATA,$result);
        }

        $result['express'] = $express['data'];
        
        return M3Result::init(ErrorCode::$OK,$result);
    }
}
