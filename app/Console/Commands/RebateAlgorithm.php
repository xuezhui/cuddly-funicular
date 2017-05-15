<?php

namespace App\Console\Commands;

use App\Tool\Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RebateAlgorithm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'algorithm:rebate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用户基金返利测试 每天晚上用户基金池里的基金减少万分之5 提现额度增加万分之5 考虑使用队列';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        //会员每天基金返利
        //每次按块取出2000条进行处理
        DB::table('fund_pool')->chunk(2000, function ($funds) {
            foreach ($funds as $item) {
                //如果不够扣除 或者 要扣除完时跳出循环
                if ($item->fund_total <= 0) {
                    continue;
                }
                $amount_origin = $item->fund_total * Config::get('rate.rebate_rate');
                //舍去法保留两位
                $amount_now = Helper::formatMoney($amount_origin);
                if ($amount_now < (0.01 / Config::get('rate.rebate_rate'))) {
                   //Log::error('用户' . $item->member_id . '的基金返利将小于0.01元 停止返利' . "\n" . $item->toJson());
                    return;
                }
                if ((float)$item->fund_total <= $amount_now) {
                    //到临界点的时候记录下日志
                    Log::error('用户' . $item->member_id . '的基金将要扣除完' . "\n" . $item->toJson());
                    //如果扣除量小于或等于剩余基金 就把扣除量设置为剩余基金量
                    $amount_now = (float)$item->fund_total;
                }
                try {
                    DB::beginTransaction();
                    //基金减少
                    DB::table('fund_pool')->where('member_id', $item->member_id)->decrement('fund_total', $amount_now);
                    $log = [
                        'user_id' => $item->member_id,
                        'amount' => $amount_now,
                        'capital_type' => 5,
                        'note' => '基金减少'.$amount_origin,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    //写入流水表
                    DB::table('capitallog')->insert($log);

                    //提现增加
                    DB::table('fund_pool')->where('member_id', $item->member_id)->increment('cash_total', $amount_now);
                    $log['capital_type'] = 6;
                    $log['note'] = '提现增加'.$amount_origin;
                    //写入流水日志
                    DB::table('capitallog')->insert($log);
                    unset($log);
                    DB::commit();
                } catch (\Exception $e) {
                    Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
                    DB::rollBack();
                }
            }
        });

        //代理每天基金返利
        //每次按块取出2000条进行处理
        DB::table('agent_fund_pool')->chunk(2000, function ($funds) {
            foreach ($funds as $item) {
                //如果不够扣除 或者 要扣除完时跳出循环
                if ($item->fund_total <= 0) {
                    continue;
                }
                $amount_origin = $item->fund_total * Config::get('rate.rebate_rate');
                //舍去法保留两位
                $amount_now = Helper::formatMoney($amount_origin);
                if ($amount_now < (0.01 / Config::get('rate.rebate_rate'))) {
                    //Log::error('代理' . $item->member_id . '的基金返利将小于0.01元 停止返利' . "\n" . $item->toJson());
                    return;
                }
                if ((float)$item->fund_total <= $amount_now) {
                    //到临界点的时候记录下日志
                    Log::error('用户' . $item->member_id . '的基金将要扣除完' . "\n" . $item->toJson());
                    //如果扣除量小于或等于剩余基金 就把扣除量设置为剩余基金量
                    $amount_now = (float)$item->fund_total;
                }
                try {
                    DB::beginTransaction();
                    //基金减少
                    DB::table('agent_fund_pool')->where('member_id', $item->member_id)->decrement('fund_total', $amount_now);
                    $log = [
                        'user_id' => $item->member_id,
                        'amount' => $amount_now,
                        'capital_type' => 5,
                        'note' => '基金减少'.$amount_origin,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    //写入流水表
                    DB::table('agent_capitallog')->insert($log);

                    //提现增加
                    DB::table('agent_fund_pool')->where('member_id', $item->member_id)->increment('cash_total', $amount_now);
                    $log['capital_type'] = 6;
                    $log['note'] = '提现增加'.$amount_origin;
                    //写入流水日志
                    DB::table('agent_capitallog')->insert($log);
                    unset($log);
                    DB::commit();
                } catch (\Exception $e) {
                    Log::error('Line: '.$e->getLine().' File: '.$e->getFile().' Message:'.$e->getMessage());
                    DB::rollBack();
                }
            }
        });
    }
}
