<?php
/**
 * Author: 陈静
 * DateTime: 2017/3/30 13:30
 * Description: 类目控制器
 *
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Entity\Product;
use Illuminate\Support\Facades\DB;
use App\Models\M3Result;
use App\Models\ErrorCode;
use App\Http\Controllers\Base;
class ProductController extends BaseController
{
    /**
     * 商品列表
     * @param  Request $request [请求参数]
     * @return [view]           [视图]
     */
    public function index(Request $request){
    	$user_id = $request->session()->get('admin', '')->id;
    	$query = DB::table('product')->where('status','<>',0);
        // $query = DB::table('product')->where('user_id',$user_id);
    	$title = $request->input('title','');
    	if(!empty($title)){
    		$query = $query->where('name','like','%'.$title.'%');
    	}
    	$starttime = $request->input('starttime','');
    	if(!empty($starttime)){
    		$query = $query->where('created_at','>=',$starttime." 00:00:00");
    	}
    	$endtime = $request->input('endtime','');
    	if(!empty($endtime)){
    		$query = $query->where('created_at','<=',$endtime." 23:59:59");
    	}
    	// $category = intval($request->input('category',0));
    	// if(!empty($category)){
    	// 	$query = $query->where('category',$category);
    	// }else{
    	// 	$pcategory = intval($request->input('pcategory',0));
    	// 	if(!empty($pcategory)){
    	// 		$cates = DB::table('category')->select('id')->where('p_id',$pcategory)->get();
	    // 		if(!empty($cates)){
	    // 			$cates = $cates->toArray();
	    // 			$query = $query->whereIn('category',$cates);
	    // 		}
    	// 	}
    		
    	// }
    	$products = $query->paginate(10);
        foreach($products as &$pro){
            $pro->cate = "";
            $pro->pcate = "";
            $cate = DB::table('category')->select('category_name','p_id')->where('id',$pro->category)->first();
            if(!empty($cate)){
                $pcate = DB::table('category')->select('category_name')->where('id',$cate->p_id)->first();
                $pro->cate = $cate->category_name;
                if(!empty($pcate)){
                    $pro->pcate = $pcate->category_name;
                }
            }
            //查询价格
            $min_price = DB::table('pro_spec')->where('product_id',$pro->id)->min('cur_price');
            $max_price = DB::table('pro_spec')->where('product_id',$pro->id)->max('cur_price');

            $pro->cur_price = $min_price.'~'.$max_price;
            
        }
        unset($pro);
    	$products->appends(['title'=>$title,'starttime'=>$starttime,'endtime'=>$endtime]);
    	return view('Admin/product/index',['products'=>$products,'request'=>$request]);
    }

    /**
     * 添加/编辑商品信息
     * @param  Request $request [数据]
     * @return [view]           [视图]
     */
    public function post(Request $request){
        //供应商列表
        $suppliers = DB::table('zh_supplier')->get();
        $level1 = DB::table('category')->whereNull('p_id')->orWhere('p_id',0)->get();
        $level2 = array();
        if($level1){ 
            $level2 = DB::table('category')->where('p_id',$level1[0]->id)->get();
        }
        
        $product = DB::table('product')->where('id',$request->input('id',0))->first();

        $user_id = $request->session()->get('admin', '')->id;
        $pid = 0;
        // $level2 = array();
        $attrs = array();
        $specs = array();
        $pros = array();
        if(!empty($product)){
            $product->imgs = unserialize($product->description);
            $pid = DB::table('category')->select('p_id')->where('id',$product->category)->first();
            if(!empty($pid->p_id)){
                $level2 = DB::table('category')->where('p_id',$pid->p_id)->get();
            }
            
            $attrs = DB::table("product_attr")->where('pro_id',$request->input('id',0))->get();
            $specs = DB::table("pro_spec")->where('product_id',$request->input('id',0))->where('status',1)->get();
            $pros = DB::table('pro_property')->where('product_id',$request->input('id',0))->lists('type');
        }
        
        //添加/编辑数据
        if($request->method()=='POST'){
            //基本信息
            $data['name'] = $request->input('name');
            $data['supplierid'] = $request->input('supplierid',0);
            $data['category'] = $request->input('cate',0);
            $data['isable'] = $request->input('isable',0);
            $data['remarks'] = $request->input('remark');
            $data['photos'] = $request->input('photos','');
            $descimg = $request->input('desc',[]);
            $data['description'] = serialize($descimg);
            $data['user_id'] = $user_id;
            if(empty($request->input('id',0))){
                $data['created_at'] = date('Y-m-d H:i:s');
                $pro_id = DB::table('product')->insertGetId($data);
            }else{
                DB::table('product')->where('id',$request->input('id',0))->update($data);
                $pro_id = $request->input('id',0);
                //图片有更新则删除原有图片资源
                if($product->photos&&$data['photos']!=$product->photos){
                    $sche = parse_url($product->photos);
                    @unlink(public_path().$sche['path']);
                }
                //详情图片删除
                if($product->imgs&&count($product->imgs)>0){
                    foreach ($product->imgs as $value) {
                        if(!in_array($value, $descimg)){
                            $sche = parse_url($value);
                            @unlink(public_path().$sche['path']);
                        }
                    }
                }
                
            }
            //商品属性
            $propertys = $request->input('params',[]);
            //删除取消了的属性
            DB::table('pro_property')->where('product_id',$pro_id)->whereNotIn('type',$propertys)->delete();
            foreach ($propertys as $value) {
                $record = DB::table('pro_property')->where('type',$value)->where('product_id',$pro_id)->first();
                if(empty($record)){
                    $prodata['type'] = $value;
                    $prodata['product_id'] = $pro_id;
                    $prodata['created_at'] = time();
                    DB::table('pro_property')->insert($prodata);
                }
            }
            //参数
            //删除已删除的参数
            $attr_keys = $request->input('param_title',[]);
            $attr_values = $request->input('param_value',[]);
            $attr_ids = $request->input('param_id',[]);
            DB::table("product_attr")->where('pro_id',$request->input('id',0))->whereNotIn('id',$attr_ids)->delete();
            //参数数据更新
            foreach ($attr_keys as $key => $value) {
                $attr = array();
                if(!empty($value)){
                    $attr['attrkey'] = $value;
                    $attr['attrvalue'] = $attr_values[$key];
                    $attr['pro_id'] = $pro_id;
                    $attr['user_id'] = $user_id;
                    if(!empty($attr_ids[$key])){
                        //更新
                        DB::table("product_attr")->where('id',$attr_ids[$key])->update($attr);
                        
                    }else{
                        //添加
                        $attr['created_at'] = date('Y-m-d H:i:s');
                        DB::table("product_attr")->insert($attr);
                    }
                }
                
            }
            
            //规格
            //删除已删除的规格
            $spec_names = $request->input('spec_name',[]);
            $spec_curprice = $request->input('spec_curprice',[]);
            $spec_marketprice = $request->input('spec_marketprice',[]);
            $spec_ids = $request->input('spec_id',[]);
            $spec_stock = $request->input('spec_stock',[]);
            DB::table("pro_spec")->where('product_id',$request->input('id',0))->whereNotIn('id',$spec_ids)->update(['status'=>0]);
            $stock = 0;
            //规格数据更新
            foreach ($spec_names as $key => $value) {
                $spec = array();
                if(!empty($value)){
                    $spec['name'] = $value;
                    $spec['cur_price'] = $spec_curprice[$key];
                    $spec['market_price'] = $spec_marketprice[$key];
                    $spec['product_id'] = $pro_id;
                    $spec['stock'] = $spec_stock[$key];
                    $spec['user_id'] = $user_id;
                    $stock += $spec_stock[$key];
                    if(!empty($spec_ids[$key])){
                        //更新
                        DB::table("pro_spec")->where('id',$spec_ids[$key])->update($spec);
                    }else{
                        //添加
                        $spec['created_at'] = date('Y-m-d H:i:s');
                        DB::table("pro_spec")->insert($spec);
                    }
                }
                
            }
            //更新商品库存
            DB::table("product")->where('id',$pro_id)->update(['stock'=>$stock]);
            return M3Result::init(ErrorCode::$OK);
        }
        return view('Admin/product/post',['level1'=>$level1,'product'=>$product,'attrs'=>$attrs,'specs'=>$specs,'pid'=>$pid,'level2'=>$level2,'pros'=>$pros,'suppliers'=>$suppliers]);
    }

    /**
     * 改变商品上下架状态
     * @param  [int] $id [商品id]
     * @return [M3Result]     [返回结果 json]
     */
    public function changestatus(Request $request){
        $id = $request->input('id',0);
        $product = DB::table('product')->where('id',$id)->first();
        if(empty($product)){
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }else{
            $update['isable'] = 1-intval($product->isable);
            DB::table('product')->where('id',$id)->update($update);
            return M3Result::init(ErrorCode::$OK);
        }
    }

    /**
     * 删除商品 假删除
     * @param  Request $request [商品id或商品id列表]
     * @return [M3Result]           [返回结果 json]
     */
    public function delete(Request $request){
        $product = DB::table('product')->where('id',$request->input('id',0))->first();
        if(empty($product)){
            $products = DB::table('product')->whereIn('id',$request->input('ids',[]))->get();
            //批量删除
            if(!empty($products)){
                foreach ($products as $value) {
                    //假删除
                    DB::table('product')->where('id',$value->id)->update(['status'=>0]);
                    
                }
                return M3Result::init(ErrorCode::$OK);
            }
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }else{
            DB::table('product')->where('id',$product->id)->update(['status'=>0]);
            return M3Result::init(ErrorCode::$OK);
        }
    }
}
