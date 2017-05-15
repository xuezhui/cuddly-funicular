<?php
/**
 * Created by PhpStorm.
 * User: NYJ
 * Date: 2017/3/27
 * Time: 14:39
 */
namespace App\Http\Controllers\Api;

use App\Entity\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ErrorCode;
use App\Models\M3Result;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function getCategoryList(Request $request)
    {
        $first_level_list = $request->input('p_id');
        $query = DB::table('category');
        if (!is_null($first_level_list))
        {
            $query = $query->where('p_id',$first_level_list);
        }
        $categoryList = $query->get();
        return M3Result::init(ErrorCode::$OK,$categoryList);
    }

    public function detail(Request $request)
    {
        $category_id = $request->input('id');
        $categoryModel = Category::where('id',$category_id)->first();

        if (is_null($categoryModel))
        {
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }

        return M3Result::init(ErrorCode::$OK,$categoryModel);
    }

    public function add(Request $request)
    {
        if (!static::issetRequest('category_name'))
        {
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        }

        $newcategoryId = DB::table('category')->insertGetId([
            'category_name' => $request->input('category_name'),
            'p_id'          => $request->input('p_id',0),
            'photos'        => $request->input('photos','http://qiniu.easyapi.cn/photo/girl'.mt_rand(18,145).'.jpg'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $newcategory = DB::table('category')->where('id', $newcategoryId)->first();

        return M3Result::init(ErrorCode::$OK,$newcategory);
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