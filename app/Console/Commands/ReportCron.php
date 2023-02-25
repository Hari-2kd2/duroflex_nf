<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Repositories\LeaveRepository;
use App\Repositories\AttendanceRepository;
use App\Http\Controllers\View\EmployeeAttendaceController;

class ReportCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report';
    protected $name      = "report";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this to create attendacne report';

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
        Log::info("Attendance cron is working fine!");
        $controller = new EmployeeAttendaceController($leaveRepository, $attendanceRepository);
        $controller->attendance();

        /*
    Write your database logic we bellow:
    Item::create(['name'=>'hello new']);
     */
    
    }
}
