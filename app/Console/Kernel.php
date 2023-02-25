<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected function schedule(Schedule $schedule)
    {

        info("schedule-run command is running fine!");
        $schedule->command('generate:attendance')->timezone('Asia/Kolkata')->daily()->at('09:00');
        $schedule->command('generate:attendance')->timezone('Asia/Kolkata')->daily()->at('11:00');
        $schedule->command('generate:attendance')->timezone('Asia/Kolkata')->daily()->at('01:00');
        $schedule->command('generate:attendance')->timezone('Asia/Kolkata')->daily()->at('18:00');
        $schedule->command('mail:daily-attendance')->timezone('Asia/Kolkata')->daily()->at('09:30');
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
