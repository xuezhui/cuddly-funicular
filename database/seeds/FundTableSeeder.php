<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class FundTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = DB::table('user')->where('id', '>', 12)->get();
        $date = [
            '2017-01-03 19:56:00',
            '2017-01-06 19:56:00',
            '2017-01-05 19:56:00',
            '2017-03-03 19:56:00',
            '2017-03-06 19:56:00',
            '2017-03-07 19:56:00',
            '2017-03-08 19:56:00',
            '2017-03-09 19:56:00',
            '2017-03-10 19:56:00',
            '2017-03-11 19:56:00',
            '2017-03-12 19:56:00',
            '2017-03-13 19:56:00',
            ];
        $input = array_rand($date);
        //dd($input);
        foreach ($users as $user) {
            $amount = random_int(1000, 99999);
            DB::table('capitallog')->insert([
                'user_id' => $user->id,
                'capital_type' => 1,
                'amount' => $amount,
                'created_at' => $date[$input],
                'note' => '充值'.$amount.'元'
            ]);
        }
    }
}
