<?php

namespace App\Jobs;

use App\AppStatics;
use App\Models\Call;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\CallRecordFile;
use League\Csv\Reader;

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
     * Runs the download process. 
     * Checks whether the file exists locally by checking the UNIX timestamp.
     * If it does then downloading will be skipped however the db entry
     * for the file is checked and created if necessary. There will always be 1 entry in the db
     * for the local file. This is by design since we do not want to overwrite the last processed line column
     * and also avoids the complexity of having to maintain multiple db file records with different attributes.
     * 
     *
     * @return void
     */
    public function handle()
    {
        
        $file_exists = Storage::disk('local')->exists(AppStatics::$CALL_RECORDS_FILENAME);
        if (!$file_exists)
            $call_records_file_uri = NULL;
        else
            $call_records_file_uri = 'storage/app/' . AppStatics::$CALL_RECORDS_FILENAME;

        if ($call_records_file_uri){
            $file_creation_timestamp = Storage::disk('local')->lastModified(AppStatics::$CALL_RECORDS_FILENAME);
            $carbon = Carbon::createFromTimestamp($file_creation_timestamp);
            if ($carbon->isToday()){
                Log::info('Call records file was updated today. Skipping download.');
                Log::info('Checking DB entry...');

                $call_records_file = CallRecordFile::latest()->first();

                if ($call_records_file != NULL){
                    Log::info('DB entry exists. Skipping creation.');
                } else {
                    $call_records_file = new CallRecordFile;
                    $call_records_file->setLastProcessedLine(0);
                    $call_records_file->setUri($call_records_file_uri);
                    $status = $call_records_file->save();
                    if ($status)
                        Log::info('DB entry for records file created');
                    else
                        Log::info('DB entry for records file failed to create');
                }

            } else {
                Log::info('Call records file found, but its not recent');
                $this->downloadFile();
            }
        } else {
            Log::info('Call records file not found in disk');
            $this->downloadFile();
        }


    }

    /**
     * Execute the job.
     * 
     * Downloads the file using the PHP system call file_get_contents 
     * File is stored locally in storage/app. Laravel has an issue with finding the file 
     * stored locally so that is why I build the file path myself. 
     * Ideally an AWS instance would be better for this and Laravel automatically takes
     * care of the connection as long as the config is defined in Laravel config.php
     * 
     * Note: When using heroku almost always the file won't exist since it has en ephemeral filesystem.
     * This is why it would be ideal for files to be stored somewhere else and not on heroku.
     * 
     *
     * @return void
     */
    private function downloadFile(){
        Log::info('Downloading call records file...');
        $contents = file_get_contents($this->file_url);
        Storage::disk('local')->put(AppStatics::$CALL_RECORDS_FILENAME, $contents);

        $call_records_file_count = CallRecordFile::count();

        if ($call_records_file_count == 0){ //genesis file does not exist
            $call_records_file = new CallRecordFile;
            $call_records_file->setUri('storage/app/' . AppStatics::$CALL_RECORDS_FILENAME);
            $call_records_file->setLastProcessedLine(0);
            $status = $call_records_file->save();

            if ($status){
                Log::info('Call records db entry created successfully');
            } else {
                Log::info('Call records db entry failed to create');
            }
        } else {
            Log::info('Call records db entry exists. Skipping creation.');
        }
        


    }
}
