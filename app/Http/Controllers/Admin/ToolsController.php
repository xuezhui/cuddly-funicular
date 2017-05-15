<?php

namespace App\Http\Controllers\Admin;

use App\Tool\Validate\Aes;
use Illuminate\Http\Request;

use App\Http\Requests;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ToolsController extends BaseController
{
    //资金转移
    public function movefund(){
        set_time_limit(0);
        $oldmoney = DB::table('a')->leftJoin('b','a.member_id','=','b.id')->leftJoin('c','b.id','=','c.member_id')->select('id','a.telephone','a.nick_name','a.real_name','b.vcurrency1','b.vcurrency2','c.now_point')->orderBy('b.id','asc')->get();
        foreach ($oldmoney as $value) {
            $nowmember = DB::table('user')->where('telephone',$value->telephone)->first();
            if(empty($nowmember)){
                $memberid = DB::table('user')->insertGetId([
                        'telephone' => $value->telephone,
                        'pay_password' =>  Hash::make(substr($value->telephone,-6)),
                        'password' => Hash::make('123456'),
                        'nickname'=> $value->nick_name,
                        'realname'=> $value->real_name,
                        'is_member' => 0,
                        'member_level' => 0,//待定字段
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')]);

            }else{
                $memberid = $nowmember->id;
            }
            $fundrecord = DB::table('fund_pool')->where('member_id',$memberid)->first();
            $funddata = array();
            $funddata['member_id'] = $memberid;
            $funddata['cash_total'] = $value->vcurrency1;
            $funddata['coupon_total'] = $value->vcurrency2;
            if($value->now_point){
                $funddata['fund_total'] = $value->now_point;
            }
            if(empty($fundrecord)){
                $funddata['created_at'] = date('Y-m-d H:i:s');
                DB::table('fund_pool')->insert($funddata);
            }else{
                $funddata['updated_at'] = date('Y-m-d H:i:s');
                DB::table('fund_pool')->where('member_id',$memberid)->update($funddata);
            }
            //代理现金清零
            $agentrecord = DB::table('agent')->where('phone',$value->telephone)->first();
            if(!empty($agentrecord)){
                $agentfundrecord = DB::table('agent_fund_pool')->where('agent_id',$agentrecord->id)->first();
                if(!empty($agentfundrecord)){
                    DB::table('agent_fund_pool')->where('id',$agentfundrecord->id)->update(['cash_total'=>0]);
                }
            }
        }
    }
    //转移会员
    public function movelink(){
        // $level1 = DB::table('user')->where('p_id',0)->select('nickname','realname','agent_id')->orderBy('agent_id','desc')->get();
        $level2 = DB::select('SELECT id,p_id,nickname,realname,agent_id FROM user WHERE p_id=id');
        foreach ($level2 as $value) {
            $l1 = DB::table('user')->where(['agent_id'=>$value->agent_id,'p_id'=>0])->first();
            if(!empty($l1)){
                DB::table('user')->where('id',$value->id)->update(['p_id'=>$l1->id]);
            }else{
                DB::table('user')->where('id',$value->id)->update(['p_id'=>0]);
            }
        }
    }

    public function merge(){
        $this->movefund();
        $this->index();
        $this->movelink();
    }
    public function index()
    {
        set_time_limit(0);
        if(session('admin_role')->name == '超级管理员'){

            // //add agents
            // $agents = DB::table('b')->leftJoin('a','b.telephone','=','a.telephone')->where('b.grade_no','>','0')->orderBy('a.member_id','asc')->get();
            // // dd($agents);
            // foreach ($agents as $agent){

            //     if(is_object($agent)){
            //         $res = DB::table('agent')->where('phone', $agent->telephone)->orWhere('agentname',$agent->nick_name)->first();

            //         if(!empty($res))
            //             continue;

            //         DB::beginTransaction();
            //         //agent表
            //         $agentid = DB::table('agent')->insertGetId([
            //             'agentname' => !emptyString($agent->real_name)?$agent->real_name:$agent->nick_name,
            //             'phone' => $agent->telephone,
            //             'agentaddr' => '',
            //             'note' => '',
            //             'create_date' => time()]);

            //         //admin表
            //         $admindata['agent_id'] = $agentid;
            //         $admindata['mobile'] = $agent->telephone;
            //         $admindata['name'] = !emptyString($agent->real_name)?$agent->real_name:$agent->nick_name;
            //         $admindata['password'] = md5('123456');
            //         $admindata['created_date'] = time();
            //         $admin_id = DB::table('admin')->insertGetId($admindata);

            //         //加入admin_role 表
            //         $roledata['admin_id'] = $admin_id;
            //         $roledata['role_id'] = 5;
            //         $roledata['created_date'] = time();
            //         DB::table('admin_role')->insert($roledata);

            //         //加入agent_fund_pool表
            //         $agentfunddata['agent_id'] = $agentid;
            //         $agentfunddata['cash_total'] = $agent->vcurrency1;
                    
            //         DB::table('agent_fund_pool')->insert($agentfunddata);
            //         //添加初始化记录
            //         DB::table('agent_capitallog')->insert(['agent_id'=>$agentid,'capital_type'=>1,'amount'=>$agent->vcurrency1,'note'=>'数据迁移代理现金初始化','created_at'=>date('Y-m-d H:i:s')]);
            //         DB::commit();
            //     }
            // }

            //add members
            $members = DB::table('a')->leftJoin('b','a.telephone','=','b.telephone')->orderBy('a.member_id','asc')->get();

            foreach ($members as $member){
                if(is_object($member)){
                    $res = DB::table('user')->where('telephone', $member->telephone)->first();
                    if(!empty($res))
                        continue;
                    DB::beginTransaction();

                    unset($res);
                    $res = DB::table('c')->where('member_id', $member->member_id)->get();
                    // $is_member = empty($res) ? 0:1;
                    
                    $memberid = DB::table('user')->insertGetId([
                        'telephone' => $member->telephone,
                        'pay_password' =>  Hash::make(substr($member->telephone,-6)),
                        'password' => Hash::make('123456'),
                        'nickname'=> $member->nick_name,
                        'realname'=> $member->real_name,
                        'is_member' => 0,
                        'member_level' => 0,//待定字段
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')]);

                    //加入fund_pool表
                    $funddata['member_id'] = $memberid;
                    $funddata['cash_total'] = $member->vcurrency1;
                    $funddata['coupon_total'] = $member->vcurrency2;

                    $res = DB::table('c')->where('member_id', $member->member_id)->first();
                    if(!empty($res)){
                        $funddata['fund_total'] = $res->now_point;
                    }else
                        $funddata['fund_total'] = 0;

                    DB::table('fund_pool')->insert($funddata);
                    //添加资金流水记录 来源去向：1-充值返购物券，2-线上或线下购物券消费 3-分销返利 4-提现 5-（自动）基金每天减少 6-（自动）提现每天增加 7-代理返利（已独立到代理返利表） 8-充值返基金 9-服务会员服务 10-供应商 11-服务会员提现
                    if($member->vcurrency2>0){
                        DB::table('capitallog')->insert(['user_id'=>$memberid,'capital_type'=>1,'amount'=>$member->vcurrency2,'created_at'=>date('Y-m-d H:i:s'),'note'=>'数据迁移会员购物券初始化']);
                    }
                    if($member->vcurrency1>0){
                        DB::table('capitallog')->insert(['user_id'=>$memberid,'capital_type'=>6,'amount'=>$member->vcurrency1,'created_at'=>date('Y-m-d H:i:s'),'note'=>'数据迁移会员现金初始化']);
                    }
                    if($funddata['fund_total']>0){
                        DB::table('capitallog')->insert(['user_id'=>$memberid,'capital_type'=>8,'amount'=>$funddata['fund_total'],'created_at'=>date('Y-m-d H:i:s'),'note'=>'数据迁移会员基金初始化']);
                    }
                    
                    DB::commit();
                }
            }
            //添加关系
            $level1 = DB::table('a')->leftJoin('user','user.telephone','=','a.telephone')->select(
                        'a.agent_id','user.id','a.telephone','a.pid','a.ppid','a.member_id','user.related_str')->get();
            
            foreach ($level1 as $value) {
                //获取父id
                $pid = $value->id;
                $pmember = array();
                if(!empty($value->pid)){
                    $pmember = DB::table('a')->leftJoin('user','user.telephone','=','a.telephone')->select(
                        'user.agent_id','user.id','a.telephone','a.pid','a.ppid','a.member_id','user.related_str')->where('member_id',$value->pid)->first();
                    if(!empty($pmember)){
                        $pid = $pmember->id;
                    }
                }

                //获取祖父id
                $agentrecord = DB::table('agent')->where('phone',$value->telephone)->whereNotIn('id',[124,125,126,127,128,129,132,133])->first();
                $gpid = 0;
                if(!empty($pmember)&&!empty($pmember->pid)&&!empty($agentrecord)){
                    $gpmember = DB::table('a')->leftJoin('user','user.telephone','=','a.telephone')->where('member_id',$pmember->pid)->first();
                    if(!empty($gpmember)){
                        $gpid = $gpmember->id;
                    }
                }
                //获取agent id
                $agentid = 0;
                
                if(empty($agentrecord)){
                    if($pmember && !empty($pmember->agent_id)){
                        $agentid = $pmember->agent_id;
                    }else{
                        $c = $value;
                        $pp = DB::table('a')->where('member_id',$c->pid)->first();
                        $agent = array();
                        while($pp&&empty($agent)){
                            $c = $pp;
                            $pp = DB::table('a')->where('member_id',$c->pid)->first();
                            $agent = DB::table('agent')->where('phone',$pp->telephone)->whereNotIn('id',[124,125,126,127,128,129,132,133])->first();
                        }
                        if(!empty($pp)&&empty($agent)){
                            $agent = DB::table('agent')->where('phone',$pp->telephone)->first();
                            $agentid = $agent->id;
                        }
                    }
                    //获取related_str
                    $related_str = "";
                    if(empty($pmember)){
                        $related_str = $value->id;
                    }else{
                        $related_str = $pmember->related_str.'-'.$value->id;
                    }
                }else{
                    $agentid = $agentrecord->id;
                    $pid = 0;
                    $gpid = 0;
                    $related_str = $value->id;
                }
                
                
                DB::table('user')->where('telephone',$value->telephone)->update(['p_id'=>$pid,'gp_id'=>$gpid,'related_str'=>$related_str,'agent_id'=>$agentid]);

            }
        }else{
            dd('只有超级管理员才能执行以上操作！');
        }
    }

    //添加关系的递归方法
    private function addRelate($root){
        //更新现系统中的关系
        $c_member = DB::table('user')->where('telephone',$root->telephone)->first();

        $children = DB::table('a')->where('pid',$root->member_id)->get();
        if(empty($children)){
            return;
        }else{
            foreach ($children as $value) {
                $child = DB::table('user')->where('telephone',$value->telephone)->first();
                DB::table('user')->where('telephone',$value->telephone)->update(['p_id'=>$c_member->id,'gp_id'=>$c_member->p_id,'agent_id'=>$c_member->agent_id,'related_str'=>$c_member->related_str.'-'.$child->id]);

                $this->addRelate($value);
            }
        }
    }

    //验证是否正确
    public function verify(){
        //验证代理 现金是否匹配
        // $oldagents = DB::table('b')->select('telephone','vcurrency1')->where('grade_no','>',0)->orderBy('telephone','desc')->get();
        // var_dump($oldagents);
        // $newagents = DB::table('agent')->leftJoin('agent_fund_pool','agent_fund_pool.agent_id','=','agent.id')->select('agent.phone','agent_fund_pool.cash_total')->orderBy('agent.phone','desc')->get();
        // var_dump($newagents);
        //验证会员 资金是否匹配
        // $oldmembers = DB::table('b')->leftJoin('c','b.id','=','c.member_id')->select('telephone','vcurrency1','vcurrency2','now_point')->where('vcurrency1','>',0)->orWhere('vcurrency2','>',0)->orWhere('now_point','>',0)->orderBy('telephone','asc')->get();
        // var_dump($oldmembers);
        $newmembers = DB::table('user')->leftJoin('fund_pool','user.id','=','fund_pool.member_id')->select('user.telephone','fund_pool.cash_total','fund_pool.coupon_total','fund_pool.fund_total')->where('fund_pool.cash_total','>',0)->orWhere('fund_pool.coupon_total','>',0)->orWhere('fund_pool.fund_total','>',0)->orderBy('telephone','asc')->get();
        var_dump($newmembers);
        // 验证会员关系是否匹配
        // $oldrelate  = DB::table('a')->select('telephone','related_str')->where('member_grade',0)->orderBy('telephone','asc')->get();
        // foreach ($oldrelate as &$value) {
        //     $value->link = "";
        //     if(!empty($value->related_str)){
        //         $ids = explode('-', $value->related_str);
        //         $tels = array();
        //         foreach ($ids as $id) {
        //             $record = DB::table('a')->select('telephone')->where('member_id',$id)->where('member_grade',0)->first();
        //             if(!empty($record)){
        //                 $tels[] = $record->telephone;
        //             }
                    
        //         }
        //         $value->link = implode('-', $tels);
        //     }
        // }
        // unset($value);
        // var_dump($oldrelate);
        // $newrelate = DB::table('user')->select('telephone','related_str')->orderBy('telephone','asc')->get();
        // foreach ($newrelate as &$value) {
        //     $value->link = "";
        //     if(!empty($value->related_str)){
        //         $ids = explode('-', $value->related_str);
        //         $tels = array();
        //         foreach ($ids as $id) {
                    
        //             $record = DB::table('user')->select('telephone')->where('id',$id)->first();
        //             if(!empty($record)){
        //                 $tels[] = $record->telephone;
        //             }
                    
        //         }
        //         $value->link = implode('-', $tels);
        //     }
        // }
        // unset($value);
        // var_dump($newrelate);
        // 验证代理关系
        // $oldagents = DB::table('a')->where('member_grade',0)->orderBy('telephone','asc')->get();
        // $olds = array();
        // foreach ($oldagents as $value) {
        //     $temp = array('telephone'=>$value->telephone,'agtel'=>'');
        //     if($value->pid){
        //         $AG = DB::table('a')->where('member_id',$value->pid)->where('member_grade','>',0)->first();
        //         if(empty($AG)&&$value->ppid){
        //             $AG = DB::table('a')->where('member_id',$value->ppid)->where('member_grade','>',0)->first();
        //         }
        //     }
        //     if(empty($AG)&&!empty($value->agent_id)){
        //         $AG = DB::table('a')->where('member_id',$value->agent_id)->where('member_grade','>',0)->first();
        //     }
        //     if(!empty($AG)){
        //         $temp['agtel'] = $AG->telephone;
        //     }
        //     $olds[] = $temp;
        // }
        // // var_dump($olds);
        // $newagents = DB::table('user')->orderBy('telephone','asc')->get();
        // $news = array();
        // foreach ($newagents as $value) {
        //     $temp = array('telephone'=>$value->telephone,'agtel'=>'');
        //     $agent = DB::table('agent')->where('id',$value->agent_id)->first();
        //     if($agent){
        //         $temp['agtel'] = $agent->phone;
        //     }
            
        //     $news[] = $temp;
        // }
        // var_dump($news);
    }
}
