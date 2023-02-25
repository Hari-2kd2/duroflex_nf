<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OvertimeRuleConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $time = Carbon::now();
        DB::table('overtime_rules')->truncate();
        DB::table('overtime_rules')->insert(
            [
                ['per_min' => '1','amount_of_deduction'=>'1','created_at'=> '1','updated_at'=>'1','created_at'=>$time,'updated_at'=>$time],
            ]

        );
    }
}
