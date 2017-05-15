<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorCode;
use App\Models\M3Result;
use Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Service\FucadminController;

/**
 * Description of BaseController
 * 管理后台 基类
 * @author chensq 
 * @time 2017-04-02 19:46
 */
class BaseController extends Controller {

    //put your code here
    public function __construct() {
        $this->middleware('checkAdminLogin');

        $admin_session = Request::session()->get('admin', '');

        if ($admin_session) {
            $admin_role = DB::table('admin')
                    ->leftJoin('admin_role', 'admin.id', '=', 'admin_role.admin_id')
                    ->leftJoin('role', 'admin_role.role_id', '=', 'role.id')
                   // ->where('role.name', '=', '超级管理员')
                    ->where('admin.id', '=', $admin_session->id)
                    ->select('role.name')
                    ->first();
            
            session(['admin_role'=>$admin_role]);
        }
        $admin_role_session = Request::session()->get('admin_role', '');
       
        //验证系统菜单session
        if ($admin_session) {
            session(['parentMenu' => null, 'childMenu' => null]);
            $sysSession = Request::session()->get('parentMenu');
            if (!$sysSession) {
                $query = DB::table('menu');
                if ($admin_role_session->name !='超级管理员') {
                    $query = $query->leftJoin('role_menu', 'menu.id', '=', 'role_menu.menu_id')
                            ->leftJoin('admin_role', 'role_menu.role_id', '=', 'admin_role.role_id')
                            ->where('admin_role.admin_id', '=', $admin_session->id)
                            ->select('menu.id', 'menu.name', 'menu.parentID', 'menu.url', 'menu.icon');
                }
                $sysmenu = $query->get();
                $sysmenu = json_decode(json_encode($sysmenu), true);
                $parentList = array_reduce($sysmenu, create_function('$result, $v', '  $result[$v["parentID"]][] =$v ;return $result;'));


                $parentMenu = $parentList[0];
                unset($parentList[0]);
                $childMenu = $parentList;
                session(['parentMenu' => $parentMenu, 'childMenu' => $childMenu]);
                session(['keep', '1']);
            }
        }



        //验证操作权限
        if ($admin_session) {
            if ( $admin_role_session->name !='超级管理员') {
                $check_result = json_decode(FucadminController::checkPermission($admin_session->id), true);
                if ($check_result['errorCode'] > 0) {
                    if (Request::isMethod('post'))
                        echo json_encode($check_result);
                    elseif (Request::isMethod('get'))
                        return abort('400', '暂无权限');
                    exit;
                }
            }
        }
    }

}
