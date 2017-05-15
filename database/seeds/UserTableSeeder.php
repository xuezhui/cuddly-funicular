<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*$telephone = '15000000000';
        for ($i = 0; $i <= 100; $i++) {
            DB::table('user')->insert([
                'nickname' => str_random(10),
                'email' => str_random(10).'@gmail.com',
                'telephone' => $telephone + $i,
                'password' => bcrypt('secret'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }*/
        // 1-省级代理 2-市级代理 3-县级代理
        // 4-20为1推荐 21-30为2推荐 31-38为3推荐
        // 4推荐39-45  5推荐46  6推荐47 7推荐48 8推荐49
        // 21推荐50-56 22推荐 57-60
        // 31推荐 70-73
        foreach (range(4, 20) as $uid) {
            DB::table('user')->where('id', $uid)->update([
                'p_id' => 1,
                'related_str' => '1-'.$uid
            ]);
        }

        foreach (range(21, 30) as $uid) {
            DB::table('user')->where('id', $uid)->update([
                'p_id' => 2,
                'related_str' => '2-'.$uid
            ]);
        }

        foreach (range(31, 38) as $uid) {
            DB::table('user')->where('id', $uid)->update([
                'p_id' => 3,
                'related_str' => '3-'.$uid
            ]);
        }

        foreach (range(39, 45) as $uid) {
            DB::table('user')->where('id', $uid)->update([
                'p_id' => 4,
                'gp_id' => 1,
                'related_str' => '1-4-'.$uid
            ]);
        }


            DB::table('user')->where('id', 46)->update([
                'p_id' => 5,
                'gp_id' => 1,
                'related_str' => '1-5-46'
            ]);


            DB::table('user')->where('id', 47)->update([
                'p_id' => 6,
                'gp_id' => 1,
                'related_str' => '1-6-47'
            ]);


            DB::table('user')->where('id', 48)->update([
                'p_id' => 7,
                'gp_id' => 1,
                'related_str' => '1-7-48'
            ]);


            DB::table('user')->where('id', 49)->update([
                'p_id' => 8,
                'gp_id' => 1,
                'related_str' => '1-8-49'
            ]);

        foreach (range(50, 56) as $uid) {
            DB::table('user')->where('id', $uid)->update([
                'p_id' => 21,
                'gp_id' => 2,
                'related_str' => '2-21-'.$uid
            ]);
        }
        foreach (range(57, 60) as $uid) {
            DB::table('user')->where('id', $uid)->update([
                'p_id' => 22,
                'gp_id' => 2,
                'related_str' => '2-22-'.$uid
            ]);
        }
        foreach (range(70, 73) as $uid) {
            DB::table('user')->where('id', $uid)->update([
                'p_id' => 31,
                'gp_id' => 3,
                'related_str' => '3-31-'.$uid
            ]);
        }
    }
}
