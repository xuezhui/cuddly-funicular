<?php

namespace App\Http\Controllers\Admin;

use App\Entity\Admin;
use App\Http\Controllers\Controller;
use App\Models\M3Result;
use App\Models\ErrorCode;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Service\ExpressController;
use Session;

class OrderController extends AuthController
{

    //订单列表
    public function index(Request $request){
        $query = DB::table('zh_order_item');
        if($request->session()->get('is_supplier')){
            $query = $query->where('supplierid',$request->session()->get('supplier_id'));
        }
        $starttime = $request->input('starttime','');
        if(!empty($starttime)){
            $query = $query->where('created_at','>=',$starttime." 00:00:00");
        }
        $endtime = $request->input('endtime','');
        if(!empty($endtime)){
            $query = $query->where('created_at','<=',$endtime." 23:59:59");
        }
        $pro = $request->input('process');
        if(!empty($pro)){
            if($pro==1){
                $query = $query->where('payment_status',0);
            }elseif($pro==2){
                $query = $query->where('payment_status',1)->where('logistics_status',0);
            }elseif($pro==3){
                $query = $query->where('payment_status',1)->where('logistics_status',1);
            }elseif($pro==4){
                $query = $query->where('payment_status',1)->where('logistics_status',2);
            }
        }
        $list = $query->orderBy('created_at','desc')->paginate(10);
        $list->appends(['process'=>$pro,'starttime'=>$starttime,'endtime'=>$endtime]);
        foreach($list as &$vo){
            $parent = DB::table('zh_order')->where('id',$vo->order_id)->first();
            $vo->payMethod = $this->getPayMethod($parent->payment_method);
            $vo->payStatus = $this->getPayStatus($vo->payment_status);
            $vo->logisticsStatus = $this->get_logistics_status($vo->logistics_status);
            $vo->address = "";
            $vo->realname = "";

            $user = DB::table('user')->select('realname','telephone')->where('id',$parent->customer_id)->first();
            if($user){
                $vo->realname = implode('/',array($user->realname,$user->telephone));
            }
            $shop_address = DB::table('shop_address')->where('id',$parent->shop_address_id)->first();
            if($shop_address){
                $vo->address = $shop_address->user_address;
            }
            
        }
        unset($vo);
        $process = ['1'=>'未付款','2'=>'已付款','3'=>'已发货','4'=>'已完成'];
        return view('Admin/order/index',['list'=>$list,'process'=>$process]);
    }

    //订单详情
    public function detail(Request $request){

        $order_id = $request->input('id',0);

        //订单详情
        $query = DB::table('zh_order_item')->where('id',$order_id);
        if($request->session()->get('is_supplier')){
            $query = $query->where('supplierid',$request->session()->get('supplier_id'));
        }
        $detail = $query->first();
        if(empty($detail)){

        }else{
            $parent = DB::table('zh_order')->where('id',$detail->order_id)->first();
            //快递情况
            if(!empty($detail->logistics_status)&&$detail->logistics_type){
                $companys = config('expresscom');
                $detail->logistics_type = $companys[$detail->logistics_type];
            }
            $detail->payMethod = $this->getPayMethod($parent->payment_method);
            $detail->payStatus = $this->getPayStatus($detail->payment_status);
            // $detail->logistics_status = $this->get_logistics_status($detail->logistics_status);
            $detail->address = "";
            $detail->realname = "";
            $detail->mobile = "";
            $detail->email = "";
            $detail->avatar = "";

            $user = DB::table('user')->select('realname','telephone','email','avatar')->where('id',$detail->member_id)->first();
            if($user){
                $detail->realname = $user->realname;
                $detail->mobile = $user->telephone;
                $detail->email = $user->email;
                $detail->avatar = $user->avatar;
            }
            $shop_address = DB::table('shop_address')->where('id',$parent->shop_address_id)->first();
            if($shop_address){
                $detail->address = $shop_address->user_address;
            }


            //订单产品列表
            $product_list = DB::table('pro_order_item')->where('order_id',$order_id)->get();
            foreach($product_list as &$vo){
                $vo->name = '';
                $vo->remarks = '';
                $vo->photos = '';
                $product = DB::table('product')->where('id',$vo->pro_id)->first();
                if($product){
                    $vo->name = $product->name;
                    $vo->photos = $product->photos;
                }
                $spec = DB::table('pro_spec')->where('id',$vo->spec_id)->first();
                $vo->spec = $spec;
            }
            unset($vo);
        }
        
        
        return view('Admin/order/detail',['list'=>$product_list,'detail'=>$detail]);
    }

    /**
     * 确认发货
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function confirmsend(Request $request){
        $order_id = $request->input('id',0);

        //订单详情
        $query = DB::table('zh_order_item')->where('id',$order_id);
        if($request->session()->get('is_supplier')){
            $query = $query->where('supplierid',$request->session()->get('supplier_id'));
        }
        $order = $query->where('payment_status',1)->first();
        if($request->method()=='POST'){
            if(empty($order)){
                return M3Result::init(ErrorCode::$DATA_EMPTY);
            }else{
                $data['logistics_type'] = $request->input('type');
                $data['logistics_number'] = $request->input('number');
                $data['logistics_status'] = 1;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['delivery_at'] = date('Y-m-d H:i:s');
                DB::table('zh_order_item')->where('id',$order->id)->update($data);
            }
            return M3Result::init(ErrorCode::$OK);
        }
        return view('Admin/order/tpl/sendmodal',['coms'=>config('expresscom'),'order'=>$order]);
            
    }

    public function express(Request $request){
        $order_id = $request->input('id',0);
        $order = DB::table('zh_order_item')->select('logistics_type','logistics_number')->where('id',$order_id)->first();
        if(empty($order)){
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }else{
            $express = ExpressController::poll(array('com'=>$order->logistics_type,'num'=>$order->logistics_number));
            $express = array_reverse(json_decode($express,true));
        }
        if(strtolower($express['message'])!='ok'){
            $express['data'][] = ['context'=>$express['message'],'time'=>''];
        }
        return view('Admin.order.tpl.express',['express'=>$express['data']]);
    }

    private function getPayMethod($status){
        switch ($status){
            case 1:
                $text = '购物券';
                break;
            default:
                $text = '';
        }
        return $text;
    }


    private function getPayStatus($status){
        switch ($status){
            case 1:
                $text = '已支付';
                break;
            case 2:
                $text = '待续';
                break;
            default:
                $text = '未支付';
        }
        return $text;
    }


    private function get_logistics_status($status){
        switch ($status){
            case 1:
                $text = '待收货';
                break;
            case 2:
                $text = '已确认收货';
                break;
            default:
                $text = '待发货';
        }
        return $text;
    }

}
