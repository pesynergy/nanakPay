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
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->call('\App\Http\Controllers\CronController@sessionClear')->everyTenMinutes();
        $schedule->call('\App\Http\Controllers\CronController@passwordClear')->everyTenMinutes();
        $schedule->call('\App\Http\Controllers\CronController@payoutClear')->everyTenMinutes();
        $schedule->call('\App\Http\Controllers\CronController@payoutProcessClear')->everyMinute();
        $schedule->call('\App\Http\Controllers\CronController@utrcodeupdate')->daily();
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
