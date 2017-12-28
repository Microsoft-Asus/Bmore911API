<?php

namespace App\Jobs;

use App\AppStatics;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadCallRecordsFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    var $file_url = "https://data.baltimorecity.gov/api/views/xviu-ezkt/rows.csv?accessType=DOWNLOAD";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Log::info('Downloading call records file...');
        $contents = file_get_contents($this->file_url);
        Storage::disk('local')->put(AppStatics::$CALL_RECORDS_FILENAME, $contents);

    }
}
