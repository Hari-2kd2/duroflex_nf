<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected function schedule(Schedule $schedule)
    {

        info("schedule-run command is running fine!");
        $schedule->command('generate:attendance')->daily()->at('09:00');
        $schedule->command('generate:attendance')->daily()->at('11:00');
        $schedule->command('generate:attendance')->daily()->at('01:00');
        $schedule->command('generate:attendance')->daily()->at('18:00');
        $schedule->command('mail:daily-attendance')->daily()->at('09:30');
        $schedule->command('user:termination')->daily()->at('16:10')->withoutOverlapping()->runInBackground()->onOneServer();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }

    protected function scheduleTimezone()
    {
        return 'Asia/Kolkata';
    }
}