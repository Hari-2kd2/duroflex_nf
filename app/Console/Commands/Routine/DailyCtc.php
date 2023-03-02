<?php

namespace App\Console\Commands\Routine;

use Illuminate\Console\Command;
use App\Http\Controllers\Payroll\SalaryController;
use App\Http\Controllers\Payroll\DailyCtcController;

class DailyCtc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:ctc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily CTC Calculation';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected $salaryController;

    public function __construct( SalaryController $salaryController)
    {
        parent::__construct();
        $this->salaryController = $salaryController;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = date('Y-m-d', strtotime('-1 days'));
        $fn = new DailyCtcController($this->salaryController);
        $fn->calculate_ctc($date);
    }
}