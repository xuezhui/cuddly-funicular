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

class FilmslideController extends Controller
{
    public function getFilmslideList(Request $request)
    {
        $query = DB::table('filmslide');
        $query = $query->where('isable',1);
        $filmslideList = $query->paginate(10);
        return M3Result::init(ErrorCode::$OK, $filmslideList);
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