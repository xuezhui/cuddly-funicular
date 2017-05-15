<?php
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Http\Controllers\Base;
use Illuminate\Support\Facades\DB;
use App\Entity\Member;
class IndexController extends BaseController
{
    public function index()
    {
        $count_charge = 0;
        $count_cash = 0;
        $count_fund = 0;
        $count_pay = 0;
        $results = [];

        if(session('admin_role')->name == '超级管理员'){
            $count_charge = DB::table('trade_order')->where(['trade_type' => 1, 'trade_status' => 2])
                ->sum('amount');
            if(empty($count_charge))
                $count_charge = 0;

            $count_cash_member = DB::table('fund_pool')->sum('cash_total');
            if(empty($count_cash_member))
                $count_cash_member = 0;

            $count_cash_agent = DB::table('agent_fund_pool')->sum('cash_total');

            if(empty($count_cash_agent))
                $count_cash_agent = 0;

            $count_cash = $count_cash_agent + $count_cash_member;

            $count_fund_member = DB::table('fund_pool')->sum('fund_total');
            if(empty($count_fund_member))
                $count_fund_member = 0;

            $count_fund_agent = DB::table('agent_fund_pool')->sum('fund_total');
            if(empty($count_fund_agent))
                $count_fund_agent = 0;

            $count_fund = $count_fund_agent + $count_fund_member;

            $count_pay = DB::table('capitallog')->where('capital_type', 2)->sum('amount');
            if(empty($count_pay))
                $count_pay = 0;

            $agents = DB::table('agent')->get();

            foreach ($agents as $agent){
                unset($result);
                if(is_object($agent)){
                    $members = DB::table('user')->where('agent_id',$agent->id)->lists('id');

                    if(!empty($members)){
                        $result = DB::table('trade_order')->where(['trade_type' => 1, 'trade_status' => 2])->whereIn('user_id',$members)
                            ->sum('amount');
                    }
                }
                if(empty($result)){
                    $result = 0;
                }
                $results[$agent->agentname] = $result;
            }
        }

        return view('Admin/index', ['count_charge' => $count_charge, 'count_cash' => $count_cash, 'count_fund' => $count_fund, 'count_pay' => $count_pay, 'count_agents' => $results]);
    }
}
