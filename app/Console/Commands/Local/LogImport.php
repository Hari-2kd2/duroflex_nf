<?php

namespace App\Console\Commands\Local;

use App\Http\Controllers\View\EmployeeAttendaceController;
use App\Model\DeviceAttendanceLog;
use App\Repositories\AttendanceRepository;
use App\Repositories\LeaveRepository;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LogImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:local-log';
    protected $name = "local-log-import";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this command to import raw attendance logs for forign database';

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
        $leaveRepository = new LeaveRepository;
        $attendanceRepository = new AttendanceRepository;
        Log::info("Log cron is working fine!");

        $logInfo = DeviceAttendanceLog::max('datetime');
        $tableName = '';

        if ($logInfo != null) {
            $maxLogDatetimeString = date('Y-m-d H:i:s', strtotime($logInfo));

            // $logDatetime = new DateTime($logInfo);
            // $subMonth = $logDatetime->modify('-1 month');
            // $lastMonthDatetime = $subMonth->format('Y-m-t 00:00:00');
            // $lastMonth = $subMonth->format('Ym');

            $currentDateString = date('Y-m-01 05:30:00');
            $lastMonth = date('Ym', strtotime('-1 month'));

            if (strtotime($maxLogDatetimeString) <= strtotime($currentDateString)) {
                $tableName = 't_lg' . $lastMonth;
            }
        }
        // dd($logInfo);
        dd($maxLogDatetimeString, $currentDateString, $tableName);

        $controller = new EmployeeAttendaceController($leaveRepository, $attendanceRepository);
        $controller->fetchRawLog($tableName);
    }
}
