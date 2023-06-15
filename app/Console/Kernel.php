<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        Commands\ShutDownDevice::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('remove:future')->dailyAt('00:05')->timezone('Asia/Dhaka');
        $schedule->command('shutdown:device')->everyMinute();
        $schedule->command('adtrp:calculate')->dailyAt('00:10')->timezone('Asia/Dhaka');
        $schedule->command('dayparts:generate')->dailyAt('03:00')->timezone('Asia/Dhaka');
        $schedule->command('dayparts:generate')->dailyAt('05:00')->timezone('Asia/Dhaka');
        $schedule->command('dayparts:generate')->dailyAt('07:00')->timezone('Asia/Dhaka');
        $schedule->command('dayparts:generate')->dailyAt('08:00')->timezone('Asia/Dhaka');
        $schedule->command('dashboardGraph:generate')->dailyAt('11:56');
        $schedule->command('notification:generate')->hourly();
        $schedule->command('notificationfu:generate')->everySixHours();
        $schedule->command('notification2hr:generate')->everySixHours();
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
