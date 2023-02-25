<?php

namespace App\Console\Commands\Routine;

use App\Http\Controllers\Payroll\SalaryController;
use App\Repositories\AttendanceRepository;
use App\Repositories\PayrollRepository;
use Illuminate\Console\Command;

class PayrollGeneration extends Command
{

    protected $name = 'payroll-generation';
    protected $signature = 'generate:payroll';
    protected $description = 'Payroll Generation';
    protected $attendanceRepository;
    protected $payrollRepository;

    public function __construct(AttendanceRepository $attendanceRepository, PayrollRepository $payrollRepository)
    {
        parent::__construct();
        $this->attendanceRepository = $attendanceRepository;
        $this->payrollRepository = $payrollRepository;
    }

    public function handle()
    {

        $fdate = date('Y-m-26', strtotime('-1 months'));
        $tdate = date('Y-m-25');
        $month = date('Y-m');

        $payroll = new SalaryController($this->attendanceRepository, $this->payrollRepository);
        $payroll->generationPayrollForAllEmployee($fdate, $tdate, $month);
    }
}
