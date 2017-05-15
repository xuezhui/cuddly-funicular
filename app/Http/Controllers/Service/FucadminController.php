<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\M3Result;
use DB;
use Request;
use App\Models\ErrorCode;

/**
 * Description of fucadminController
 *
 * @author chensq
 * @time 2017-4-2 16:30
 */
class FucadminController extends Controller {

    /**
     * 检测当前账号权限
     * 获取当前账号所拥有权限
     * in_array() Request::getRequestUri()检测是否拥有
     * layer提示
     */
    static function checkPermission($adminID) {
        if (!$adminID) {
            return M3Result::init(ErrorCode::$ADMIN_NOT_EXIST);
        }
        $adminDetail = DB::table('admin')->where('id', '=', $adminID)->first();
        if (!$adminDetail) {
            return M3Result::init(ErrorCode::$ADMIN_NOT_EXIST);
        }
        $adminRole = DB::table('admin_role')->where('admin_id', '=', $adminID)->get();
        if (!$adminRole) {
            return M3Result::init(ErrorCode::$ADMINROLE_NOT_EXIST);
        }
        $adminPermission = DB::table('permission')
                        ->leftJoin('role_permission', 'permission.id', '=', 'role_permission.permission_id')
                        ->leftJoin('admin_role', 'role_permission.role_id', '=', 'admin_role.role_id')
                        ->select('permission.route_name')
                        ->where('admin_role.admin_id', '=', $adminID)->get();
        if (!$adminPermission) {
            return M3Result::init(ErrorCode::$ADMINPERMISSION_NOT_EXIST);
        }
        $adminPermission = json_decode(json_encode($adminPermission), true);
        $adminPermission = array_reduce($adminPermission, create_function('$result, $v', '  $result[] = $v["route_name"];return $result;'));

        $now_route = '/' . Request::path();


        if (!in_array($now_route, $adminPermission)) {
            return M3Result::init(ErrorCode::$ADMINPERMISSION_NOT_EXIST);
        }
        return M3Result::init(ErrorCode::$OK);
    }

    //put your code here
}
