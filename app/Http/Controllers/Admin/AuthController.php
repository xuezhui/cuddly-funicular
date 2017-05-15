<?php

namespace App\Http\Controllers\Admin;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
/**
 * Description of AuthController
 * 管理后台 权限
 * @author 陈静 
 * @time 2017-04-24 10:32
 */
class AuthController extends BaseController
{
    //put your code here
    public function __construct() {
    	parent::__construct();
        //判断是否是供应商
        $admin_session = Request::session()->get('admin', '');
        if($admin_session){
        	$record = DB::table('zh_supplier')->where('admin_id',$admin_session->id)->first();
	        if($record){
	        	session(['is_supplier'=>true,'supplier_id'=>$record->id]);
	        }
        }
        
    }
}
