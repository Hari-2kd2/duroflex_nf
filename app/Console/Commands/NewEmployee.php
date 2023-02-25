<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Repositories\LeaveRepository;
use App\Repositories\AttendanceRepository;
use App\Http\Controllers\View\EmployeeAttendaceController;

class NewEmployee extends Command
{
   
    protected $signature = 'employee:cron';
    protected $name      = "employee-cron";

  
    protected $description = 'Run to create newly added employee report';

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
        Log::info("New Employee cron is working fine!");
        $controller = new EmployeeAttendaceController($this->leaveRepository,$this->attendanceRepository);
        $controller->samsungNewEmployees();

  
    }
}
