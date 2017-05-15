<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\M3Result;
use App\Models\ErrorCode;
use App\Http\Controllers\Base;
class FilmslideController extends BaseController
{
    /**
     * 幻灯片列表
     * @return [view] [视图]
     */
    public function index(Request $request){
    	
    	//更新排序
    	if($request->method()=="POST"){
    		$displayorders = $request->input('displayorder',[]);
    		foreach ($displayorders as $key => $value) {
    			DB::table('filmslide')->where('id',$key)->update(['displayorder'=>$value]);
    		}
    	}
    	$slides = DB::table('filmslide')->orderBy('displayorder','asc')->paginate(10);
    	return view('Admin/filmslide/index',['slides'=>$slides]);
    }

    /**
     * 添加/编辑 幻灯片数据
     * @param  Request $request [请求数据]
     * @return [view]           [视图]
     */
    public function post(Request $request){
    	$slide = DB::table('filmslide')->where('id',$request->input('id',0))->first();
    	//添加/编辑数据
    	if($request->method()=='POST'){
    		$data['title'] = $request->input('title','');
    		$data['photos'] = $request->input('photos','');
    		$data['link'] = $request->input('link','');
    		$data['isable'] = $request->input('isable',0);
    		$data['displayorder'] = $request->input('displayorder',0);
    		if(empty($request->input('id',0))){//增加数据
    			$data['created_at'] = time();
    			DB::table('filmslide')->insert($data);
    		}else{//更新数据
    			$data['updated_at'] = time();
    			DB::table('filmslide')->where('id',$request->input('id',0))->update($data);
    			//图片有更新则删除原有图片资源
    			if($slide->photos&&$slide->photos!=$data['photos']){
                    $sche = parse_url($slide->photos);
    				@unlink(public_path().$sche['path']);
    			}
    		}
    		return M3Result::init(ErrorCode::$OK);
    	}
    	return view('Admin/filmslide/post',['slide'=>$slide]);
    }
    /**
     * 删除幻灯片
     * @param  Request $request [请求数据]
     * @return [M3Result]           [结果 json]
     */
    public function delete(Request $request){
    	$slide = DB::table('filmslide')->where('id',$request->input('id',0))->first();
    	if(empty($slide)){
    		//批量删除
    		$slides = DB::table('filmslide')->whereIn('id',$request->input('ids',[]))->get();
    		if(!empty($slides)){
    			//删除图片资源
	    		foreach ($slides as $s) {
                    $sche = parse_url($s->photos);
	    			if(file_exists(public_path().$sche['path'])){
	    				@unlink(public_path().$sche['path']);
	    			}
	    			DB::table('filmslide')->where('id',$s->id)->delete();
	    		}
	    		return M3Result::init(ErrorCode::$OK);
    		}
    		return M3Result::init(ErrorCode::$DATA_EMPTY);
    	}else{
            $sche = parse_url($slide->photos);
    		if(file_exists(public_path().$sche['path'])){
				@unlink(public_path().$sche['path']);
			}
			DB::table('filmslide')->where('id',$slide->id)->delete();
			return M3Result::init(ErrorCode::$OK);
    	}
    }

    /**
     * 改变幻灯片发布状态
     * @param  [int] $id [被改变幻灯片的id]
     * @return [M3Result]     [结果 json]
     */
    public function changestatus(Request $request){
        $id = $request->input('id',0);
    	$slide = DB::table('filmslide')->where('id',$id)->first();
        if(empty($slide)){
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }else{
            $update['isable'] = 1-intval($slide->isable);
            DB::table('filmslide')->where('id',$id)->update($update);
            return M3Result::init(ErrorCode::$OK);
        }
    }
}
