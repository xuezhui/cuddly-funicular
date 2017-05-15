<?php
/**
 * Author: chensq
 * DateTime: 2017/4/24 09:30
 * Description: 供应商控制器
 *
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\M3Result;
use App\Models\ErrorCode;
use App\Http\Controllers\Base;
class SupplierController extends BaseController
{

    public function index(Request $request){
        $query = DB::table('zh_supplier');
        //添加筛选条件
    	$name = $request->input('name');
    	if(!empty($name)){
    		$query = $query->where(function($tmp) use($name){
                $tmp->orWhere('zh_supplier.suppliername','like','%'.$name.'%')->orWhere('admin.mobile','like','%'.$name.'%');
            });
    	}
    	$starttime = $request->input('starttime','');
    	if(!empty($starttime)){
    		$query = $query->where('zh_supplier.create_date','>=',strtotime($starttime." 00:00:00"));
    	}
    	$endtime = $request->input('endtime','');
    	if(!empty($endtime)){
    		$query = $query->where('zh_supplier.create_date','<=',strtotime($endtime." 23:59:59"));
    	}
        $supplier = $query  ->select('zh_supplier.*','admin.mobile')
                            ->leftJoin('admin','zh_supplier.admin_id','=','admin.id')
                            ->get();
        return view('Admin.supplier.index',['request'=>$request,'supplier'=>$supplier]);
    }

    public function add(Request $request){
        $detail = array();

        if (isset($request['id'])) {
            $detail = DB::table('zh_supplier')
                    ->leftJoin('admin','zh_supplier.admin_id','=','admin.id')
                    ->where('zh_supplier.id','=',$request['id'])
                    ->select('zh_supplier.*','admin.mobile')
                    ->first();
            
        }
        $role_query = DB::table("role");
        if(!empty($detail)){
            $role_query->leftJoin('admin_role','role.id','=','admin_role.role_id')->select("role.id","role.name")->where('admin_role.admin_id',$detail->id);
        }else{
            $role_query->select('id','name');
        }
        $roles = $role_query->get();
        return view('Admin.supplier.post', ['detail' => $detail,'roles'=>$roles]);
    }

    public function addDo(Request $request){
        
        //supplier表
        $supplierdata['suppliername'] = $request->input('suppliername','');
        $supplierdata['note'] = $request->input('note','');
        
        //admin表
        $admindata['mobile'] = $request->input('phone','');
        $admindata['name'] = $supplierdata['suppliername'];
        if(!empty($request->input('password',''))){
            $admindata['password'] = md5($request->input('password',''));
        }
      
        if (isset($request['id']) && !empty($request['id'])) {
            DB::beginTransaction();
            //更新
             //更新zh_supplier表
            
            $supplierdata['update_date'] = time();
            $supplier_update = DB::table("zh_supplier")->where("id",$request['id'])->update($supplierdata);
            
            
            if(!$supplier_update){
                DB::rollBack();
                return M3Result::init(ErrorCode::$DB_ERROR);
            }
            $admin_id = DB::table('zh_supplier')->where("id",$request['id'])->select('admin_id')->first();
            
            //更新admin表
            $admindata['updated_date'] = time();
            $admin_update = DB::table('admin')->where('id',$admin_id->admin_id)->update($admindata);
            if(!$admin_update){
                DB::rollBack();
                return M3Result::init(ErrorCode::$DB_ERROR);
            }
            DB::commit();
        }else{
            //插入
             DB::beginTransaction();
             $record = DB::table('admin')->where('name',$supplierdata['suppliername'])->orWhere('mobile',$admindata['mobile'])->get();
            if(count($record)>0){
                return M3Result::init(ErrorCode::$DB_ERROR);
            }
            $admindata['created_date'] = time();
            $admin_id = DB::table('admin')->insertGetId($admindata);
            if(!$admin_id){
                DB::rollBack();
                return M3Result::init(ErrorCode::$DB_ERROR);
            }
            //加入 supplier 表
            $supplierdata['create_date'] = time();
            $supplierdata['admin_id'] = $admin_id;
            $supplier_id = DB::table('zh_supplier')->insertGetId($supplierdata);
            if(!$supplier_id){
                DB::rollBack();
                return M3Result::init(ErrorCode::$DB_ERROR);
            }
            
            //加入admin_role 表
            $roledata['admin_id'] = $admin_id;
            $roledata['role_id'] = $request->input('role',0);
            $roledata['created_date'] = time();
            $adin_roleID = DB::table('admin_role')->insertGetId($roledata);
            if(!$adin_roleID){
                DB::rollBack();
                return M3Result::init(ErrorCode::$DB_ERROR);
            } 
            DB::commit();
        }
        return M3Result::init(ErrorCode::$OK);
    }
   
}
