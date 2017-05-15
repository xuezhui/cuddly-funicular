<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Entity\Admin\Admin;
use App\Models\ErrorCode;
use App\Models\M3Result;
use Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests;
use App\Http\Controllers\Base;

/**
 * Description of AdminController
 * 管理员管理
 * @author chensq
 * @time 2017-03-30 14:47:00
 */
class AdminController extends BaseController {

//    public function __construct() {
//        $check_result = json_decode(FucadminController::checkPermission(24),true);
//        if($check_result['errorCode']>0)
//        {
//            if(Request::isMethod('post'))
//                echo json_encode($check_result);
//            elseif(Request::isMethod('get'))
//                return abort('400','暂无权限');
//            exit;
//        } 
//    }
    //管理员列表
    public function lists() {
        $a = 1; //测试git
        $query = DB::table('admin')
                ->leftJoin('admin_role', 'admin.id', '=', 'admin_role.admin_id')
                ->leftJoin('role', 'admin_role.role_id', '=', 'role.id')
                ->select('admin.*', 'role.name as rolename');
        $getData = Request::all();

        if (isset($getData['datemin']) && !empty($getData['datemin']))
            $query->where('admin.created_date', '>=', $getData['datemin']);
        if (isset($getData['datemax']) && !empty($getData['datemax']))
            $query->where('admin.created_date', '<=', $getData['datemax']);
        if (isset($getData['name']))
            $query->where('admin.name', 'like', $getData['name'] . '%');
        if (isset($getData['id']))
            $query->where('admin.id', '=', $getData['id']);
        $lists = $query->paginate(20);


        return view('Admin.admin', ['lists' => $lists]);
    }

    /**
     * 添加管理员 页面展示
     * @return type
     */
    public function addadmin() {
        $role_list = $detail = array();
        $getData = Request::all();

        if (isset($getData['id'])) {
            $query = DB::table('admin')
                    ->leftJoin('admin_role', 'admin.id', '=', 'admin_role.admin_id')
                    ->leftJoin('role', 'admin_role.role_id', '=', 'role.id')
                    ->select('admin.*', 'admin_role.role_id', 'role.name as rolename')
                    ->where('admin.id', '=', $getData['id']);
            $detail = $query->first();
        }
        //列出所有角色
        $role_list = DB::table('role')->select('id', 'name', 'description')->get();
//        var_dump($detail);exit;
        return view('Admin.admin_add', ['detail' => $detail, 'role_list' => $role_list]);
    }

    /**
     * 添加管理员 执行动作 待优化
     * @return type
     */
    public function addadminDo() {
        $postData = Request::all();


        if (isset($postData['id']) && !empty($postData['id'])) {
            DB::beginTransaction();
            //更新    
            $update_arr = array();
            $update_arr = array('mobile' => $postData['phone'], 'description' => $postData['description'], 'updated_date' => time());
            if ($postData['password'] == $postData['password2'] && !empty($postData['password']) && !empty($postData['password2'])) {
                $update_arr['password'] = md5($postData['password']);
            }
            $admin_update = DB::table('admin')->where('id', $postData['id'])->update($update_arr);
            if (!$admin_update) {
                DB::rollBack();
                return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
            }
            $adminrole_update_arr = array('role_id' => $postData['adminRole'], 'updated_date' => time());
            $adminrole_update = DB::table('admin_role')->where('admin_id', $postData['id'])->update($adminrole_update_arr);
            if (!$adminrole_update) {
                DB::rollBack();
                return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
            }
            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        } else {
            DB::beginTransaction();
            //插入
            if ($postData['password'] != $postData['password2'] || empty($postData['password']) || empty($postData['password2'])) {
                return M3Result::init(ErrorCode::$PARAM_ERROR);
            }
            $insert_arr = array('name' => $postData['name'], 'mobile' => $postData['phone'], 'password' => md5($postData['password']), 'description' => $postData['description'], 'created_date' => time());
            $adminID = DB::table('admin')->insertGetId($insert_arr);
            if (!$adminID) {
                DB::rollBack();
                return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
            }
            $admin_role = array('admin_id' => $adminID, 'role_id' => $postData['adminRole'], 'created_date' => time());
            $admin_role_result = DB::table('admin_role')->insert($admin_role);
            if (!$admin_role_result) {
                DB::rollBack();
                return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
            }
            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        }
    }

    /**
     * 设置管理员有效
     * @return type
     */
    public function validDo() {
        $postData = Request::all();
        if (!$postData['id'])
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        $query = DB::table('admin')
                ->where('id', $postData['id'])
                ->update(['valid' => $postData['status']]);

        if ($query)
            return M3Result::init(ErrorCode::$OK);
        else
            return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
    }

    /**
     * 删除管理员
     * @return type
     */
    public function deladmin() {
        $postData = Request::all();

        if (!$postData['id'])
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        DB::beginTransaction();
        //删除主表
        $del_admin = DB::table('admin')->where('id', '=', $postData['id'])->delete();
        if (!$del_admin) {
            DB::rollBack();
            return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        }
        //删除角色表

        $del_admin_role = DB::table('admin_role')->where('admin_id', '=', $postData['id'])->delete();
        if (!$del_admin_role) {
            DB::rollBack();
            return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        }
        DB::commit();
        return M3Result::init(ErrorCode::$OK);
    }

    /**
     * 角色列表
     * 返回角色下的所有管理员
     * @return type
     */
    public function role() {

        $query = DB::table('role');
        $lists = $query->paginate(20);
        return view('Admin.role', ['lists' => $lists]);
    }

    /**
     * 添加角色
     * @return type
     */
    public function addrole() {
        $getData = Request::all();
        $menu = $permission_list = $role_permission = $detail = array();
        if (isset($getData['id'])) {
            $query = DB::table('role')
                    ->where('id', '=', $getData['id']);
            $detail = $query->first();
            //查询该角色的拥有权限
            $role_permission = DB::table('role_permission')->where('role_id', '=', $getData['id'])->select('permission_id')->get();
            $role_permission = json_decode(json_encode($role_permission), true);
            $role_permission = array_reduce($role_permission, create_function('$result, $v', '  $result[] = $v["permission_id"];return $result;'));
        }


        //列出所有权限
        $permission_list = DB::table('permission')->select('id', 'menu_id', 'name', 'description')->get();
        $permission_list = json_decode(json_encode($permission_list), true);
        $permission_list_arr = array_reduce($permission_list, create_function('$result, $v', '  $result[$v["menu_id"]][] = $v;return $result;'));

        $menu = DB::table('menu')->select('id', 'name')->get();
        $menu = json_decode(json_encode($menu), true);
        $menu = array_reduce($menu, create_function('$result, $v', '  $result[$v["id"]]= $v["name"];return $result;'));

        return view('Admin.role_add', ['detail' => $detail, 'permission_list_arr' => $permission_list_arr, 'menu' => $menu, 'role_permission' => $role_permission]);
    }

    /**
     * 执行添加角色
     * @return type
     */
    public function addroleDo() {
        $postData = Request::all();
        /**
         * array(
         *  array('role_id'=>$roleID,'permission_id'=>$permission_ids[$k]),
         * array('role_id'=>$roleID,'permission_id'=>$permission_ids[$k]),
         * )
         */
        if (isset($postData['id']) && !empty($postData['id'])) {
            //更新   
            DB::beginTransaction();
            $update_arr = array('name' => $postData['name'], 'description' => $postData['description'], 'updated_date' => time());
            $update_role = DB::table('role')->where('id', $postData['id'])->update($update_arr);
            if (!$update_role) {
                DB::rollBack();
                return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
            }
            $is_delete = DB::table('role_permission')->where('role_id', '=', $postData['id'])->first();
            if ($is_delete) {
                //删除role_permission
                $del_role_permission = DB::table('role_permission')->where('role_id', '=', $postData['id'])->delete();
                if (!$del_role_permission) {
                    DB::rollBack();
                    return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
                }
            }
            //插入role_permission
            $permission_id = $postData['permission'];
            $permission_ids = array();
            foreach ($permission_id as $key => $permissionID) {
                $permission_ids[$key] = array('role_id' => $postData['id'], "permission_id" => $permissionID, "created_date" => time());
            }
            $insert_role_permission = DB::table('role_permission')->insert($permission_ids);
            if (!$insert_role_permission) {
                DB::rollBack();
                return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
            }
            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        } else {
            //插入
            if (empty($postData['name'])) {
                return M3Result::init(ErrorCode::$PARAM_ERROR);
            }
            DB::beginTransaction();
            $insert_arr = array('name' => $postData['name'], 'description' => $postData['description'], 'created_date' => time());
            //插入role表
            $roleID = DB::table('role')->insertGetId($insert_arr);
            if (!$roleID) {
                DB::rollBack();
                return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
            }
            $permission_id = $postData['permission'];
            $permission_ids = array();
            foreach ($permission_id as $key => $permissionID) {
                $permission_ids[$key] = array('role_id' => $roleID, "permission_id" => $permissionID, "created_date" => time());
            }
            //插入role_permission
            $insert_role_permission = DB::table('role_permission')->insert($permission_ids);
            if (!$insert_role_permission) {
                DB::rollBack();
                return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
            }
            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        }
    }

    /**
     * 设置角色菜单 权限
     * @return type
     */
    public function addrolemenu() {
        $getData = Request::all();
        $menu = $menu_list = $role_menu = $detail = array();
        if (isset($getData['id'])) {
            $query = DB::table('role')
                    ->where('id', '=', $getData['id']);
            $detail = $query->first();
            //查询该角色的拥有权限
            $role_menu = DB::table('role_menu')->where('role_id', '=', $getData['id'])->select('menu_id')->get();
            $role_menu = json_decode(json_encode($role_menu), true);
            $role_menu = array_reduce($role_menu, create_function('$result, $v', '  $result[] = $v["menu_id"];return $result;'));
        }
        //列出所有菜单
        $menu_list = DB::table('menu')->select('id', 'name', 'parentID')->get();
        $menu_list = json_decode(json_encode($menu_list), true);
        $menu_list_arr = array_reduce($menu_list, create_function('$result, $v', '  $result[$v["parentID"]][] = $v;return $result;'));

        return view('Admin.rolemenu_add', ['detail' => $detail, 'menu_list_arr' => $menu_list_arr, 'role_menu' => $role_menu]);
    }

    public function addrolemenuDo() {
        $postData = Request::all();
        if (isset($postData['role_id']) && !empty($postData['role_id'])) {
            //更新   
            DB::beginTransaction();
            $is_delete = DB::table('role_menu')->where('role_id', '=', $postData['role_id'])->first();
            if ($is_delete) {
                //删除 role_menu
                $del_role_menu = DB::table('role_menu')->where('role_id', '=', $postData['role_id'])->delete();
                if (!$del_role_menu) {
                    DB::rollBack();
                    return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
                }
            }
            //插入 role_menu
            if (@$postData['menu']) {
                $menu_id = $postData['menu'];
                $menu_ids = array();
                foreach ($menu_id as $key => $menuID) {
                    $menu_ids[$key] = array('role_id' => $postData['role_id'], "menu_id" => $menuID, "created_date" => time());
                }
                $insert_role_menu = DB::table('role_menu')->insert($menu_ids);
                if (!$insert_role_menu) {
                    DB::rollBack();
                    return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
                }
            }
            DB::commit();
            return M3Result::init(ErrorCode::$OK);
        }
    }

    /**
     * 删除角色
     * @return type
     */
    public function delrole() {
        $postData = Request::all();
        if (!$postData['id'])
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        DB::beginTransaction();
        //删除role
        $del_role = DB::table('role')->where('id', '=', $postData['id'])->delete();
        if (!$del_role) {
            DB::rollBack();
            return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        }
        //删除 role_menu
        $del_role_menu = DB::table('role_menu')->where('role_id', '=', $postData['id'])->delete();
        if (!$del_role_menu) {
            DB::rollBack();
            return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        }
        //删除 role_permission
        $del_role_permission = DB::table('role_permission')->where('role_id', '=', $postData['id'])->delete();
        if (!$del_role_permission) {
            DB::rollBack();
            return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        }
        //删除 admin_role
        $del_admin_role = DB::table('admin_role')->where('role_id', '=', $postData['id'])->delete();
        if (!$del_admin_role) {
            DB::rollBack();
            return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        }
        DB::commit();
        return M3Result::init(ErrorCode::$OK);
    }

    //权限管理
    public function permission() {
        $lists = array();
        $query = DB::table('permission');

        $postData = Request::all();
        if (isset($postData['name']) && !empty($postData['name'])) {
            $name = $postData['name'];
            $query = $query->where(function($query) use($name) {
                $query->orWhere('name', 'like', '%' . $name . '%');
            });
        }
        $lists = $query->paginate(10);
        return view('Admin.permission', ['lists' => $lists]);
    }

    public function addpermission() {
        $detail = array();
        $getData = Request::all();

        if (isset($getData['id'])) {
            $query = DB::table('permission')
                    ->where('id', '=', $getData['id']);
            $detail = $query->first();
        }
        $menuLists = DB::table('menu')->where('parentID', '=', '0')->get();

        return view('Admin.permission_add', ['detail' => $detail, 'menuLists' => $menuLists]);
    }

    public function addpermissionDo() {
        $postData = Request::all();
//        var_dump($postData);exit;
        if (isset($postData['id']) && !empty($postData['id'])) {
            //更新    
            $update_arr = array();
            $update_arr = array('name' => $postData['name'], 'route_name' => $postData['route_name'], 'menu_id' => $postData['menu_id'], 'description' => $postData['description'], 'updated_date' => time());

            if (DB::table('permission')->where('id', $postData['id'])->update($update_arr))
                return M3Result::init(ErrorCode::$OK);
            else
                return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        } else {
            //插入
            if (empty($postData['route_name']) || empty($postData['name'])) {
                return M3Result::init(ErrorCode::$PARAM_ERROR);
            }
            $insert_arr = array('name' => $postData['name'], 'menu_id' => $postData['menu_id'], 'route_name' => $postData['route_name'], 'description' => $postData['description'], 'created_date' => time());
            if (DB::table('permission')->insert($insert_arr))
                return M3Result::init(ErrorCode::$OK);
            else
                return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        }
    }

    public function delpermission() {
        $postData = Request::all();
        if (!$postData['id'])
            return M3Result::init(ErrorCode::$PARAM_ERROR);
        DB::beginTransaction();
        //删除 permission
        $del_permission = DB::table('permission')->where('id', '=', $postData['id'])->delete();
        if (!$del_permission) {
            DB::rollBack();
            return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        }
        //删除 role_permission
        $del_role_permission = DB::table('role_permission')->where('permission_id', '=', $postData['id'])->delete();
        if (!$del_role_permission) {
            DB::rollBack();
            return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        }
        DB::commit();
        return M3Result::init(ErrorCode::$OK);
    }

    //系统设置
    /**
     * 系统菜单列表
     * @return type
     */
    public function sysmenu() {
        $lists = array();
        $query = DB::table('menu');

        $postData = Request::all();
        if (isset($postData['name']) && !empty($postData['name'])) {
            $name = $postData['name'];
            $query = $query->where(function($query) use($name) {
                $query->orWhere('name', 'like', '%' . $name . '%')->orwhere('id', '=', $name);
            });
        }

        $lists = $query->orderBy('sort', 'desc')->get(); //->paginate(10);
        $lists = json_decode(json_encode($lists), true);
        $lists = array_reduce($lists, create_function('$result, $v', '  $result[$v["parentID"]][] = $v;return $result;'));
        return view("Admin.sysmenu", ['lists' => $lists]);
    }

    /**
     * 系统添加菜单
     * @return type
     */
    public function addsysmenu() {
        $detail = array();
        $getData = Request::all();

        if (isset($getData['id'])) {
            $query = DB::table('menu')
                    ->where('id', '=', $getData['id']);
            $detail = $query->first();
        }

        $menulist = DB::table('menu')->select('id', 'name', 'level', 'parentID')->where('level', '=', 0)->get();
        return view("Admin.sysmenu_add", ['detail' => $detail, 'menulist' => $menulist]);
    }

    /**
     * 系统菜单添加 执行动作
     * @return type
     */
    public function addsysmenuDo() {
        $postData = Request::all();
//        var_dump($postData);exit;
        $level = 0;
        if (isset($postData['parentID'])) {
            if ($postData['parentID'] > 0)
                $level = 1;
        }
        if (isset($postData['id']) && !empty($postData['id'])) {
            //更新    
            $update_arr = array();
            $update_arr = array('name' => $postData['name'], 'parentID' => $postData['parentID'], 'icon' => $postData['icon'], 'level' => $level, 'url' => $postData['url'], 'sort' => $postData['sort'], 'updated_date' => time());

            if (DB::table('menu')->where('id', $postData['id'])->update($update_arr))
                return M3Result::init(ErrorCode::$OK);
            else
                return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        } else {
            //插入
            if (empty($postData['name'])) {
                return M3Result::init(ErrorCode::$PARAM_ERROR);
            }

            $insert_arr = array('name' => $postData['name'], 'parentID' => $postData['parentID'], 'icon' => $postData['icon'], 'level' => $level, 'url' => $postData['url'], 'sort' => $postData['sort'], 'created_date' => time());
            if (DB::table('menu')->insert($insert_arr))
                return M3Result::init(ErrorCode::$OK);
            else
                return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        }
    }

    /**
     * 系统惨带删除
     * @return type
     */
    public function delsysmenu() {
        $postData = Request::all();
        if (!$postData['id'])
            return M3Result::init(ErrorCode::$PARAM_ERROR);

        DB::beginTransaction();
        //删除 menu
        $del_menu = DB::table('menu')->where('id', '=', $postData['id'])->delete();
        if (!$del_menu) {
            DB::rollBack();
            return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        }
        //删除 role_menu
        $del_role_menu = DB::table('role_menu')->where('menu_id', '=', $postData['id'])->delete();
        if (!$del_role_menu) {
            DB::rollBack();
            return M3Result::init(ErrorCode::$UNKNOWN_ERROR);
        }
        DB::commit();
        return M3Result::init(ErrorCode::$OK);
    }

}
