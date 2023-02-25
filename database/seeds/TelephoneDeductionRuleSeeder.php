<?php

use Carbon\Carbon;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TelephoneDeductionRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $time = Carbon::now();
        DB::table('telephone_allowance_deduction_rules')->truncate();
        DB::table('telephone_allowance_deduction_rules')->insert(
            [
                ['cost_per_call' => '5','limit_per_month'=>'100','created_at'=>$time,'updated_at'=>$time],
            ]

        );
    }
}
