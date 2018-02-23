<?php

namespace App\Jobs;

use App\AppStatics;
use App\Models\Call;
use App\Models\CallRecordFile;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Statement;
use Carbon\Carbon;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

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
        $file_path = NULL;
        $call_records_file = NULL;

        if (App::environment('local')) {
            $exists = Storage::disk('local')->exists(AppStatics::$CALL_RECORDS_FILENAME_MINI);
            if ($exists){
                $call_records_file = CallRecordFile::where('uri', 'storage/app/' . AppStatics::$CALL_RECORDS_FILENAME_MINI)->first();
                if (!$call_records_file){ // if it doesn't exist
                    $call_records_file = new CallRecordFile;
                    $call_records_file->setUri('storage/app/' . AppStatics::$CALL_RECORDS_FILENAME_MINI);
                    $call_records_file->save();
                }
            }
            $file_path = 'storage/app/' . AppStatics::$CALL_RECORDS_FILENAME_MINI;
        } else {
            $call_records_file = CallRecordFile::latest()->first();
            var_dump($call_records_file);
            return;
            if ($call_records_file){
                $exists = Storage::disk('local')->exists(AppStatics::$CALL_RECORDS_FILENAME);
                $file_path = $call_records_file->getUri();
            } else {
                $exists = false;
                Log::info('No DB entry found for the latest downloaded call records file');
            }
        }

        if (!$exists){
            Log::info('Records file does not exist.');
        } else {

            $last_processed_line = $call_records_file->getLastProcessedLine();
            $last_bpd_call_id = NULL;

            if ($last_processed_line == NULL)
                $last_processed_line = 0;

            Log::info('Starting from last processed line #: ' . $last_processed_line);

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


            $reader = Reader::createFromPath($call_records_file->getUri(), 'r');
            $reader->setHeaderOffset(0);
            $stmt = (new Statement())->offset($last_processed_line);
            $records = $stmt->process($reader);

            $output = new ConsoleOutput();
            $progress = new ProgressBar($output, count($reader));
            $progress->start();

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

                if ($bpd_call_id == 'null' || empty($bpd_call_id) || !Carbon::parse($call_time)->isCurrentYear()){
                    // Skip this one
                    $progress->advance();
                    $records_skipped++;
                    continue;
                }

                $record_exists = Call::where('bpd_call_id', $bpd_call_id)->first();

                if ($record_exists){
                    // Skip this one
                    $progress->advance();
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
                    $last_bpd_call_id = $bpd_call_id;
                    $call_records_file->setLastProcessedLine($record_count);
                    $call_records_file->setLastProcessedBPDCallId($last_bpd_call_id);
                    $call_records_file->save();
                }

                $progress->advance();

            }

            $progress->finish();

            if ($call_records_file->getLastProcessedLine() == count($reader)){
                Log::info('Database has the latest call records. Processing skipped.');
            }

            Log::info('Processing complete.');
            Log::info('Record count: ' . $record_count);
            Log::info('Records added: ' . $records_added);
            Log::info('Records skipped: ' . $records_skipped);
            Log::info('Records failed to add: ' . $records_failed_to_add);
        }

    }
}
