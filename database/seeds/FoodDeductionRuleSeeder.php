<?php

use Carbon\Carbon;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodDeductionRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $time = Carbon::now();
        DB::table('food_allowance_deduction_rules')->truncate();
        DB::table('food_allowance_deduction_rules')->insert(
            [
                ['breakfast_cost' => '30','lunch_cost' => '50','dinner_cost' => '30','created_at'=>$time,'updated_at'=>$time],
            ]

        );
    }
}
