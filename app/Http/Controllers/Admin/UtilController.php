<?php
/**
 * Author: 陈静
 * DateTime: 2017/3/29 18:00
 * Description: 工具控制器
 *
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class UtilController extends Controller
{
    //验证是否已登录
    public function __construct() {
        $this->middleware('checkAdminLogin');
    }
    /**
     * 文件上传工具
     * @param  Request $request [description]
     * @return [string]           [文件路径]
     */
    public function uploadfile(Request $request){
    	$file = $request->file('file');
    	
	   
      	//Move Uploaded File
      	$destinationPath = 'uploads/'.date('Ymd');
      	$filename = str_random(15).'.'.$file->getClientOriginalExtension();
      	while(file_exists(public_path('uploads').'/'.$filename)){
      		$filename = str_random(15).'.'.$file->getClientOriginalExtension();
      	}
      	$file->move($destinationPath,$filename);
      	return 'http://'.$_SERVER['HTTP_HOST'].'/'.$destinationPath.'/'.$filename;
    }
}
