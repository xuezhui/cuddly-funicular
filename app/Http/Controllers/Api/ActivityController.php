<?php
/**
 * Created by PhpStorm.
 * User: NYJ
 * Date: 2017/4/8
 * Time: 13:28
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\ErrorCode;
use App\Models\M3Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller 
{
    public function getVersion(){
      
       $result = array('version'=>1,'name'=>'xcmb','url'=>'http://180.153.105.145/imtt.dd.qq.com/16891/7CE6D80EE2E382922C70406A46C42864.apk?mkey=59029ab8864096c9&f=9383&c=0&fsname=com.bxtai.app_1.0.1_1.apk&hsr=4d5s&p=.apk' );
       return M3Result::init(ErrorCode::$OK,$result);
    }
    public function getActList(Request $request)
    {
        $query = DB::table('activity')->where(['status' => 1, 'isable' => 1]);

        $timestart = strtotime(date('Y-m-d').' 00:00:00');
        $query->where('created_at','>=',$timestart);

        $activities = $query->get();
        if(empty($activities)){
            $activities = DB::table('activity')->where(['status' => 1, 'isable' => 1])->limit(5)->get();
        }
        $wantResults = [];
        foreach ($activities as $_vA)
        {
            $_tmp['id']          = $_vA ->id;
            $_tmp['user_id']     = $_vA ->user_id;
            $_tmp['name']        = $_vA ->name;
            $_tmp['photos']      = $_vA ->photos;
            $_tmp['description'] = unserialize($_vA ->description);
            $_tmp['remarks']     = $_vA ->remarks;
            $_tmp['created_at']  = $_vA ->created_at;
            $_tmp['updated_at']  = $_vA ->updated_at;
            $_tmp['status']      = $_vA ->status;
            $_tmp['isable']      = $_vA ->isable;

            array_push($wantResults,$_tmp);
        }

        return M3Result::init(ErrorCode::$OK,$wantResults);
    }
    //最新一张每日专题图片
    public function getDefaultImg(Request $request){
        $active = DB::table('activity')->where(['status' => 1, 'isable' => 1])->orderBy('created_at','desc')->first();
        if(empty($active)){
            return M3Result::init(ErrorCode::$OK);
        }
        return M3Result::init(ErrorCode::$OK,$active->photos);
    }
}