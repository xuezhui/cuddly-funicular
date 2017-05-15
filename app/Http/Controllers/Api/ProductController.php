<?php
/**
 * Created by PhpStorm.
 * User: NYJ
 * Date: 2017/3/27
 * Time: 14:39
 */
namespace App\Http\Controllers\Api;

use App\Entity\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ErrorCode;
use App\Models\M3Result;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function getExplosions(Request $request)
    {
        $query = DB::table('pro_property')
            ->where('type',1)->get();//->simplePaginate(10)暂不分页

        $productIDsArr = [];
        foreach ($query as $_tmp)
        {
            array_push($productIDsArr,$_tmp->product_id);
        }
        $products = DB::table('product')->whereIn('id', $productIDsArr)->where('status', '<>', 0)->get();
        foreach ($products as $_tmp)
        {
            $price_max_with_min = DB::table('pro_spec')
                ->select(DB::raw('max(cur_price) as max_price, min(cur_price) as min_price'))
                ->where('product_id', $_tmp->id)
                ->first();
            $_tmp->min_price = $price_max_with_min->min_price;
            $_tmp->max_price = $price_max_with_min->max_price;

        }
        return M3Result::init(ErrorCode::$OK,$products);
    }

    /**
     * 商品列表
     * @param Request $request
     * @return string
     */
    public function getProductList(Request $request)
    {
        $category = $request->input('category', 0);
        $name = $request->input('name', '');//根据商品名称搜索
        $query = DB::table('product')->where(['status' => 1, 'isable' => 1]);
        if ($category > 0) {
            $query = $query->where('category', (int)$category);
        }
        if ($name != '') {
            $query = $query->where('name', 'like', '%'.$name.'%');
        }
        $productList = $query->orderBy('created_at', 'desc')->paginate(20);
        foreach ($productList as $_tmp)
        {
            $price_max_with_min = DB::table('pro_spec')
                ->select(DB::raw('max(cur_price) as max_price, min(cur_price) as min_price'))
                ->where('product_id', $_tmp->id)
                ->first();
            $_tmp->min_price = $price_max_with_min->min_price;
            $_tmp->max_price = $price_max_with_min->max_price;
            $_tmp->c_photos = $_tmp->photos;
        }
        return M3Result::init(ErrorCode::$OK,$productList);
    }

    public function detail(Request $request)
    {
        $product_id = $request->input('id');
        $productModel = Product::where('id',$product_id)->first();
        if (is_null($productModel))
        {
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }

        //获取最大和最小价格
        $price_max_with_min = DB::table('pro_spec')
            ->select(DB::raw('max(cur_price) as max_price, min(cur_price) as min_price'))
            ->where(['product_id' => $product_id, 'status' => 1])
            ->first();

        //获得供应商信息
        $supplier = DB::table('zh_supplier')->where('id', $productModel->supplierid)->first();
        $count = DB::table('pro_order_item')->where('pro_id', $product_id)
            ->where('created_at', '>=', date('Y-m-01 00:00:00'))
            ->where('created_at', '<=', date('Y-m-d 23:59:59'))->count();

        $productModel->description = unserialize($productModel->description);
        $productModel->min_price = $price_max_with_min->min_price;
        $productModel->max_price = $price_max_with_min->max_price;
        $productModel->count = $count;
        if(!empty($supplier)){
            $productModel->supplier_name = $supplier->suppliername;
            $productModel->seller = $supplier->seller;
        }

        return M3Result::init(ErrorCode::$OK,$productModel);
    }

    public function add(Request $request)
    {
        /*名称	类型	说明	是否必填	示例	默认值
        name	string	产品名称	是	小黄鱼	小黄鱼
        salemode	int	销售模式	是	1	1
        category	int	商品分类ID	是	5	5
        stock	int	库存	是	10	10
        salesvolume	int	销量	是	1	0
        cur_price	double	当前价格	是	1.22
        market_price	double	市场价格	是	5.55
        description	string	产品描述/详情	是	很好吃很好吃	很好吃很好吃
        photos	string	产品图片，多个用逗号隔开	否
        remarks	string	备注	否	备注批发	*/
        if (!static::issetRequest('name,salemode,category,stock,salesvolume,cur_price,market_price,description'))
        {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }

        $newProductId = DB::table('product')->insertGetId([
            'name'         => $request->input('name'),
            'salemode'     => $request->input('salemode'),
            'category'     => $request->input('category'),
            'stock'        => $request->input('stock'),
            'salesvolume'  => $request->input('salesvolume'),
            'cur_price'    => $request->input('cur_price'),
            'market_price' => $request->input('market_price'),
            'description'  => $request->input('description'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $newProduct = DB::table('product')->where('id', $newProductId)->first();

        return M3Result::init(ErrorCode::$OK,$newProduct);
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