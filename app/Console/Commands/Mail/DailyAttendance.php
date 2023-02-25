<?php

namespace App\Console\Commands\Mail;

use Illuminate\Console\Command;
use App\Repositories\AttendanceRepository;
use App\Http\Controllers\Mail\AttendanceMailController;

class DailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:daily-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To send daily attendance report via mail';

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
        $attendanceRepository = new AttendanceRepository;
        $mail = new AttendanceMailController($attendanceRepository);
        $mail->index();
    }
}
