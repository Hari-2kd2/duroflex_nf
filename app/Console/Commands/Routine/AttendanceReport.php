<?php

namespace App\Console\Commands\Routine;

use App\Http\Controllers\Attendance\GenerateReportController;
use App\Repositories\AttendanceRepository;
use App\Repositories\LeaveRepository;
use Illuminate\Console\Command;

class AttendanceReport extends Command
{

    protected $name = 'attendance-report';
    protected $signature = 'generate:attendance';
    protected $description = 'Attendance-Report';

    protected $leaveRepository;
    protected $attendanceRepository;

    public function __construct(LeaveRepository $leaveRepository, AttendanceRepository $attendanceRepository)
    {
        parent::__construct();
        $this->leaveRepository = $leaveRepository;
        $this->attendanceRepository = $attendanceRepository;
    }

    public function handle()
    {

        $date = date('Y-m-d', strtotime('-1 days'));
        $report = new GenerateReportController($this->leaveRepository, $this->attendanceRepository);
        $report->generateAttendanceReport($date);
        info('Report Cron Executed at - ' . date('Y-m-d H:i:s'));
    }
}
