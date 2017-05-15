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
class ActivityController extends BaseController
{
    /**
     * 专题列表
     * @param  Request $request [请求参数]
     * @return [view]           [视图]
     */
    public function index(Request $request){

        $query = DB::table('activity')->where('status','<>',0);
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

        $actives = $query->paginate(10);
        $actives->appends(['title'=>$title,'starttime'=>$starttime,'endtime'=>$endtime]);
        foreach($actives as &$a){
            $a->description = unserialize($a->description);

        }
        unset($a);

        
        return view('Admin/activity/index',['actives'=>$actives,'request'=>$request]);
    }

    /**
     * 添加/编辑专题信息
     * @param  Request $request [数据]
     * @return [view]           [视图]
     */
    public function post(Request $request){
        $activity = DB::table('activity')->where('id',$request->input('id',0))->first();

        $user_id = $request->session()->get('admin', '')->id;
        
        if(!empty($activity)){
            $activity->description = unserialize($activity->description);
        }

        //添加/编辑数据
        if($request->method()=='POST'){
            //基本信息
            $data['name'] = $request->input('name');

            $data['isable'] = $request->input('isable',0);
            $data['photos'] = $request->input('photos','');
            $descimg = $request->input('desc',[]);
            $data['description'] = serialize($descimg);
            $data['user_id'] = $user_id;
            if(empty($request->input('id',0))){
                $data['created_at'] = time();
                DB::table('activity')->insertGetId($data);
            }else{
                $data['updated_at'] = time();
                DB::table('activity')->where('id',$request->input('id',0))->update($data);
                //图片有更新则删除原有图片资源
                if($activity->photos&&$data['photos']!=$activity->photos){
                    $sche = parse_url($activity->photos);
                    @unlink(public_path().$sche['path']);
                }
                //详情图片删除
                if($activity->description&&count($activity->description)>0){
                    foreach ($activity->description as $value) {
                        if(!in_array($value, $descimg)){
                            $sche = parse_url($value);
                            @unlink(public_path().$sche['path']);
                        }
                    }
                }

            }
            
            return M3Result::init(ErrorCode::$OK);
        }
        return view('Admin/activity/post',['activity'=>$activity]);
    }

    /**
     * 改变专题上下架状态
     * @param  [int] $id [专题id]
     * @return [M3Result]     [返回结果 json]
     */
    public function changestatus(Request $request){
        $id = $request->input('id',0);
        $activity = DB::table('activity')->where('id',$id)->first();
        if(empty($activity)){
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }else{
            $update['isable'] = 1-intval($activity->isable);
            $update['updated_at'] = time();
            DB::table('activity')->where('id',$id)->update($update);
            return M3Result::init(ErrorCode::$OK);
        }
    }

    /**
     * 删除专题
     * @param  Request $request [专题id或专题id列表]
     * @return [M3Result]           [返回结果 json]
     */
    public function delete(Request $request){
        $activity = DB::table('activity')->where('id',$request->input('id',0))->first();
        if(empty($activity)){
            $activities = DB::table('activity')->whereIn('id',$request->input('ids',[]))->get();
            //批量删除
            if(!empty($activities)){
                foreach ($activities as $value) {
                    //删除图片资源
                    if($value->photos){
                        $sche = parse_url($value->photos);
                        @unlink(public_path().$sche['path']);
                    }
                    $descimg = unserialize($value->description);
                    if(count($descimg)>0){
                        foreach ($descimg as $value) {
                            if($value){
                                $sche = parse_url($value);
                                @unlink(public_path().$sche['path']);
                            }
                        }
                    }
                    //删除数据
                    DB::table('activity')->whereIn('id',$request->input('ids',[]))->delete();
                }
                return M3Result::init(ErrorCode::$OK);
            }
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }else{
            //删除图片资源
            if($activity->photos){
                $sche = parse_url($activity->photos);
                @unlink(public_path().$sche['path']);
            }
            $descimg = unserialize($activity->description);
            if(count($descimg)>0){
                foreach ($descimg as $value) {
                    if($value){
                        $sche = parse_url($value);
                        @unlink(public_path().$sche['path']);
                    }
                }
            }
            //删除数据
            DB::table('activity')->where('id',$activity->id)->delete();
            return M3Result::init(ErrorCode::$OK);
        }
    }
}
