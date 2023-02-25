<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $time = Carbon::now();

        DB::table('user')->truncate();
        DB::table('user')->insert(
            [
                ['role_id' => 1, 'user_name' => 'admin', 'password' => bcrypt('123'), 'remember_token' => str_random(10), 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => $time, 'updated_at' => $time],
            ]
        );

        // DB::table('work_shift')->truncate();
        // DB::table('work_shift')->insert(
        //     [
        //         ['shift_name' => 'Day', 'start_time' => '08:30:00', 'end_time' => '17:00:00', 'late_count_time' => '08:35:00', 'created_at' => $time, 'updated_at' => $time],
        //     ]
        // );


        // DB::table('pay_grade')->truncate();
        // DB::table('pay_grade')->insert(
        //     [
        //         ['pay_grade_name' => 'A', 'gross_salary' => '10000', 'percentage_of_basic' => 50, 'basic_salary' => '50000', 'overtime_rate' => 500, 'created_at' => $time, 'updated_at' => $time],
        //     ]
        // );


        DB::table('employee')->truncate();
        DB::table('employee')->insert(
            [

                ['user_id'      => 1, 'finger_id'                  => '1001', 'department_id' => 1, 'designation_id' => 1, 'work_shift_id'     => 1, 'first_name' => "Admin", 'pay_grade_id' => 1, 'supervisor_id' => 1,
                    'date_of_birth' => "1995-01-01", 'date_of_joining' => '2017-03-01', 'gender'  => 'Male', 'phone'     => '1838784536', 'status' => 1, 'status'     => 1, 'created_by'         => 1, 'updated_by'    => 1, 'created_at' => $time, 'updated_at' => $time],
            ]
        );
        // }

    }
}
