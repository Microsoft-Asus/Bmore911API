<?php

namespace App\Jobs;

use App\AppStatics;
use App\Models\Call;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Carbon\Carbon;

class ProcessCallRecords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Processing call records file...');

        $exists = false;
        $filename = NULL;

        if (App::environment('local')) {
            $exists = Storage::disk('local')->exists(AppStatics::$CALL_RECORDS_FILENAME_MINI);
            $filename = AppStatics::$CALL_RECORDS_FILENAME_MINI;
        } else {
            $exists = Storage::disk('local')->exists(AppStatics::$CALL_RECORDS_FILENAME);
            $filename = AppStatics::$CALL_RECORDS_FILENAME;
        }

        if (!$exists){
            Log::info('Records file does not exist.');
        } else {

            $bpd_call_id = 'null';
            $call_time = 'null';
            $priority = -1;
            $district = 'null';
            $description = 'null';
            $address = 'null';
            $latitude = 0;
            $longitude = 0;

            $record_count = 0;
            $records_added = 0;
            $records_skipped = 0;
            $records_failed_to_add = 0;


            $reader = Reader::createFromPath('storage/app/'. $filename, 'r');
            $reader->setHeaderOffset(0);
            $records = $reader->getRecords();

            foreach ($records as $offset => $record) {

                $record_count++;

                //all cell values will either exist or mapped to null by the reader
                $bpd_call_id = $record['callNumber'];
                $call_time = $record['callDateTime'];
                $priority = $record['priority'];
                $district = $record['district'];
                $description = $record['description'];
                $address = $record['incidentLocation'];
                $addrAndCoordinates = $record['location'];

                if ($bpd_call_id == 'null' || empty($bpd_call_id)){
                    // Skip this one
                    $records_skipped++;
                    continue;
                }

                $record_exists = Call::where('bpd_call_id', $bpd_call_id)->first();

                if ($record_exists){
                    // Skip this one
                    $records_skipped++;
                    continue;
                }

                if ($call_time == 'null' || empty($call_time))
                    $call_time = '0000-00-00 00:00:00';
                else {
                    $call_time = Carbon::parse($call_time)->toDateTimeString();
                }

                if ($priority == 'null' || empty($priority))
                    $priority = Call::$PRIORITY_UNKNOWN;
                else {
                    switch ($record['priority']){

                        case Call::$STRING_PRIORITY_NON_EMERGENCY: $priority = Call::$PRIORITY_NON_EMERGENCY; break;
                        case Call::$STRING_PRIORITY_LOW: $priority = Call::$PRIORITY_LOW; break;
                        case Call::$STRING_PRIORITY_MEDIUM: $priority = Call::$PRIORITY_MEDIUM; break;
                        case Call::$STRING_PRIORITY_HIGH: $priority = Call::$PRIORITY_HIGH; break;
                        default : $priority = Call::$PRIORITY_UNKNOWN;
                    }
                }

                if ($district == 'null' || empty($district))
                    $district = AppStatics::$UNKNOWN_STRING;

                if ($description == 'null' || empty($description))
                    $description = AppStatics::$UNKNOWN_STRING;

                if ($address == 'null' || empty($address))
                    $address = AppStatics::$UNKNOWN_STRING;

                if ($addrAndCoordinates == 'null' || empty($addrAndCoordinates)){
                    $latitude = 0;
                    $longitude = 0;
                } else {
                    $temp = str_after($addrAndCoordinates, "(");

                    $coordinates = str_replace(")", "", $temp);
                    $coordinates = str_replace(" ", "", $coordinates);

                    $coordinates_array = explode(",", $coordinates);

                    //Setting lat. and long.
                    if (count($coordinates_array) == 2){
                        $latitude = $coordinates_array[0];
                        $longitude = $coordinates_array[1];
                    }

                    if (!is_numeric($latitude) || !is_numeric($longitude)){
                        $latitude = 0;
                        $longitude = 0;
                    }
                }

                $call = new Call;
                $call->setBpdCallId($bpd_call_id);
                $call->setCallTime($call_time);
                $call->setPriority($priority);
                $call->setDistrict($district);
                $call->setDescription($description);
                $call->setAddress($address);
                $call->setLatitude($latitude);
                $call->setLongitude($longitude);
                $success = $call->save();

                if (!$success){
                    $records_failed_to_add++;
                } else {
                    $records_added++;
                }
            }

            Log::info('Processing complete.');
            Log::info('Record count: ' . $record_count);
            Log::info('Records added: ' . $records_added);
            Log::info('Records skipped: ' . $records_skipped);
            Log::info('Records failed to add: ' . $records_failed_to_add);
        }

    }
}
