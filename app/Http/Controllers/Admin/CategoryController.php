<?php
/**
 * Author: 陈静
 * DateTime: 2017/3/29 18:00
 * Description: 类目控制器
 *
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Entity\Category;
use Illuminate\Support\Facades\DB;
use App\Models\M3Result;
use App\Models\ErrorCode;
use App\Http\Controllers\Base;
class CategoryController extends BaseController
{	
	/**
	 * 类目列表
	 * @return [view] [视图]
	 */
    public function index(){
    	//一级类目
    	$categoryList = DB::table('category')->whereNull('p_id')->orWhere('p_id',0)->paginate(10);
    	
    	foreach ($categoryList as &$value) {
    		$childs = DB::table('category')->where('p_id',$value->id)->get();
    		$value->childs = $childs;
    	}
        unset($value);
    	return view('Admin/category/index',['categoryList'=>$categoryList]);
    }
    
    /**
     * 添加/编辑类目
     * @param  [Request] $request [请求数据]
     * @return [view]     [视图]
     */
    public function post(Request $request){
		$category = Category::where('id',$request->input('id'))->first();
		if(empty($category)){
			$pcategory = Category::where('id',$request->input('pid'))->first();
		}else{
			$pcategory = Category::where('id',$category->p_id)->first();
		}
		//提交类目信息
		if($request->method()=='POST'){
			// $category->category_name = $request->input('categoryName','');
			$data['category_name'] = $request->input('categoryName','');
			$data['p_id'] = $request->input('pid',0);
			// $data['status'] = $request->input('status',0);
			$data['photos'] = $request->input('photos','');
			if(empty($request->input('id',0))){
				$data['created_at'] = date('Y-m-d H:i:s');
				Category::insert($data);
			}else{
				Category::where('id',$category->id)->update($data);
				//删除原有图片资源
				if(!empty($category->photos)&&$category->photos!=$data['photos']){
                    $sche = parse_url($category->photos);
					@unlink(public_path().$sche['path']);
				}
			}
			return redirect('admin/category/index');
		}
		$returndata['category'] = $category;
		$returndata['pid'] = 0;
		if(!empty($pcategory)){
			$returndata['pid'] = $pcategory->id;
		}
		return view('Admin/category/post',$returndata);
    }

    /**
     * 删除类目
     * @param  [Request] $request [请求数据]
     * @return [view]     [视图]
     */
    public function delete(Request $request){
    	$category = Category::where('id',$request->input('id',0))->first();
    	//不存在该类目
    	if(empty($category)){
    		$cates = $request->input('ids');
    		if(count($cates)>0){
    			foreach ($cates as $value) {
    				$category = Category::where('id',$value)->first();
    				Category::where('id',$value)->delete();
		    		//删除图片资源
                    
		    		if(!empty($category->photos)){
                        $sche = parse_url($category->photos);
						@unlink(public_path().$sche['path']);
					}
					//如果是一级目录 则删除其下的二级目录
		    		if(empty($category->p_id)){
		    			$childs = DB::table('category')->where('p_id',$value)->get();
		    			foreach ($childs as $value) {
		    				Category::where('id',$value->id)->delete();
		    				//删除图片资源
				    		if(!empty($value->photos)){
                                $sche = parse_url($value->photos);
								@unlink(public_path().$sche['path']);
							}
		    			}
		    		}
    			}

    			return M3Result::init(ErrorCode::$OK,$cates);
    		}
    		return M3Result::init(ErrorCode::$DATA_EMPTY);
    	}else{
    		Category::where('id',$request->input('id'))->delete();
    		//如果是一级目录 则删除其下的二级目录
    		if(empty($category->p_id)){
    			$childs = DB::table('category')->where('p_id',$request->input('id'))->get();
    			foreach ($childs as $value) {
    				Category::where('id',$value->id)->delete();
    				//删除图片资源
                    
		    		if(!empty($value->photos)){
                        $sche = parse_url($value->photos);
						@unlink(public_path().$sche['path']);
					}
    			}
    		}
    		//删除图片资源
    		if(!empty($category->photos)){
                $sche = parse_url($category->photos);
				@unlink(public_path().$sche['path']);
			}
    		return M3Result::init(ErrorCode::$OK);
    	}
    }

    public function getchilds(Request $request){
        $id = $request->input('id',0);
    	$category = Category::where('id',$id)->first();
    	//不存在该类目
    	if(empty($category)){
    		return M3Result::init(ErrorCode::$DATA_EMPTY);
    	}else{
    		$childs = DB::table('category')->where('p_id',$id)->get();
    		return M3Result::init(ErrorCode::$OK,$childs);
    	}
    }
}
