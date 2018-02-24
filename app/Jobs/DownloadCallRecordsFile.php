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

                // if ($call_records_file != NULL && Carbon::parse($call_records_file->created_at)->isToday()){
                //     Log::info('DB entry exists. Skipping creation');
                // } else {
                //     $call_records_file = new CallRecordFile;

                //     //to get recent data from file I have to get the last 50,000 records once (genesis file).
                //     $call_records_file_count = CallRecordFile::count();
                //     if ($call_records_file_count == 0){ //genesis file does not exist
                //         $reader = Reader::createFromPath('storage/app/' . AppStatics::$CALL_RECORDS_FILENAME, 'r');
                //         $reader->setHeaderOffset(0);
                //         $call_records_file->setLastProcessedLine(count($reader) - 50000);
                //     }

                //     $call_records_file->setUri($call_records_file_uri);
                //     $status = $call_records_file->save();
                //     if ($status)
                //         Log::info('DB entry for records file created');
                //     else
                //         Log::info('DB entry for records file failed to create');
                // }

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

    private function downloadFile(){
        Log::info('Downloading call records file...');
        $contents = file_get_contents($this->file_url);
        Storage::disk('local')->put(AppStatics::$CALL_RECORDS_FILENAME, $contents);

        // //to get recent data from file I have to get the last 50,000 records once (genesis file).

        // $call_records_file_count = CallRecordFile::count();
        // if ($call_records_file_count == 0){ //genesis file does not exist
        //     $reader = Reader::createFromPath('storage/app/' . AppStatics::$CALL_RECORDS_FILENAME, 'r');
        //     $reader->setHeaderOffset(0);
        //     $call_records_file->setLastProcessedLine(count($reader) - 50000);
        // }
        // $status = $call_records_file->save();

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
