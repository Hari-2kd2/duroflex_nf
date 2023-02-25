<?php

namespace App\Console\Commands;

use App\Model\Employee;
use Illuminate\Console\Command;
use App\Model\EmployeeInOutData;
use Illuminate\Support\Facades\Log;
use App\Lib\Enumerations\UserStatus;

class UpdateLeaveBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'update-earn-leave-balance';

    protected $signature = 'update:leavebalance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Leave Balance Backround Process';

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
        Log::info('Cron Working' . DATE('d-m-Y h:i A'));

        $employees = Employee::where('status', UserStatus::$ACTIVE)->get();

        foreach ($employees as $key => $employee) {

            $balance = 0.00;

            $count = EmployeeInOutData::where('finger_print_id', $employee->finger_id)->where('date', 'LIKE', '%' . date('Y-m') . '%')->where('in_time', '!=', null)->count();

            if ($employee->leave_balance != null) {
                $balance = $employee->leave_balance;
            }

            if ($count >= 20) {

                info($count);
                info($employee->finger_id);
                // info((string)$balance);

                Employee::where('finger_id', $employee->finger_id)->update([
                    'leave_balance' => number_format((float)($balance) + (float)($count / 20), 2, '.', '')
                ]);
            } else {

                info($count);
                info($employee->finger_id);
                // info((string)$balance);

                Employee::where('finger_id', $employee->finger_id)->update([
                    'leave_balance' =>  number_format((float)$balance, 2, '.', '')
                ]);
            }
        }
    }
}
