<?php

namespace App\Console;

use App\Jobs\DownloadCallRecordsFile;
use App\Jobs\ProcessCallRecords;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

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

        ini_set('memory_limit','4096M');


        if (App::environment('local')){
            $schedule->job(new ProcessCallRecords());
        } else {
             // $schedule->job(new DownloadCallRecordsFile)->daily()->after(function (){
             //     ProcessCallRecords::dispatch();
             // });
           //Only for testing
           $schedule->job(new DownloadCallRecordsFile)->after(function (){
               ProcessCallRecords::dispatch();
           });
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
