<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Admin;

use App\Entity\Admin;
use App\Http\Controllers\Controller;
use App\Models\M3Result;
use App\Models\ErrorCode;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

use Session;

/**
 *用户登录
 * @author limin
 */
class LoginController extends Controller
{
    //用户登录
    public function login()
    {
        if($input = Input::all()){

            $name = $input['name'];
            $password = $input['password'];
            $keep = array_key_exists('keep', $input);

            if(empty($name) || empty($password)){
                return back()->with('msg','用户名或者密码不能为空！');
            }

            $user = Admin::where('name', $name)
                            ->where('password', md5($password))
                            ->where('valid', 1)
                            ->first();

            if(!$user){
                return back()->with('msg','用户名或者密码错误！');
            }
            session(['admin'=>$user]);
            if($keep){
                session(['keep', '1']);
            }
            else{
                session(['keep', '0']);
            }

            return redirect('/admin');

        }else{
            return view('Admin.login');
        }
    }

    //用户退出
    public function quit(){
        session(['admin'=>null]);
        session(['keep'=>'0']);
        return redirect('/admin/login');
    }
}

