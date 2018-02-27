<?php

use Dingo\Api\Routing\Router;
use App\Helpers\BMResponse;
use App\Helpers\BMValidate;
use App\Models\Call;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {

    // authentication routes
    $api->group(['prefix' => 'auth'], function(Router $api) {
        $api->post('signup', 'App\\Api\\V1\\Controllers\\SignUpController@signUp');
        $api->post('login', 'App\\Api\\V1\\Controllers\\LoginController@login');

        $api->post('recovery', 'App\\Api\\V1\\Controllers\\ForgotPasswordController@sendResetEmail');
        $api->post('reset', 'App\\Api\\V1\\Controllers\\ResetPasswordController@resetPassword');

        $api->post('logout', 'App\\Api\\V1\\Controllers\\LogoutController@logout');
        $api->post('refresh', 'App\\Api\\V1\\Controllers\\RefreshController@refresh');
        $api->get('me', 'App\\Api\\V1\\Controllers\\UserController@me');
    });

    //test routes
    $api->group(['middleware' => 'jwt.auth'], function(Router $api) {
        $api->get('protected', function() {
            return response()->json([
                'message' => 'Access to protected resources granted! You are seeing this text as you provided the token correctly.'
            ]);
        });

        $api->get('refresh', [
            'middleware' => 'jwt.refresh',
            function() {
                return response()->json([
                    'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!'
                ]);
            }
        ]);
    });

    $api->get('hello', function() {
        return response()->json([
            'message' => 'Hello, World!'
        ]);
    });

    //record endpoints currently accessible by anyone
    $api->group(['prefix' => 'records'], function(Router $api) {
        $api->post('/search', function(Request $request){

            $call_records = Call::query();
            $fromDate = "";
            $toDate = "";

            $validator = BMValidate::init($request);
            $params_present = $validator->check(['start_date', 'end_date', 'priorities', 'districts'])->getParamsPresent();

            $start_date_key = array_search('start_date', $params_present);
            $end_date_key = array_search('end_date', $params_present);
            $priorities_key = array_search('priorities', $params_present);
            $districts_key = array_search('districts', $params_present);

            //empty or missing both start_date and end_date
            if (count($params_present) == 0){
                return BMResponse::bad_request('Query missing parameters. Required: start_date, end_date | Optional: priorities[], districts[]');
            } else if ($start_date_key === FALSE && $end_date_key === FALSE){
                return BMResponse::bad_request('Query missing parameters. Required: start_date, end_date | Optional: priorities[], districts[]');
            }

            // Checks if the required json attributes are set in order to set a correct start_date and end_date.
            // A datetime MySQL format is required for a query however Carbon can parse any time format and convert it into DATETIME.
            
            //Both start_date and end_date are defined and checked.
            if ( ($start_date_key !== FALSE && $start_date_key >= 0) && ($end_date_key !== FALSE && $end_date_key >= 0)){

                $fromDate = Carbon::parse($request['start_date'], 'America/New_York')->toDateString() . " 00:00:00";
                $toDate = Carbon::parse($request['end_date'], 'America/New_York')->toDateString() . " 23:59:59";

                $call_records = $call_records->whereBetween('call_time', [$fromDate,$toDate])->orderBy('call_time');
            //only start date is defined and checked. end_date is assumed to the time the request is sent.
            } else if ($start_date_key !== FALSE && $start_date_key >= 0){
                
                $fromDate = Carbon::parse($request['start_date'], 'America/New_York')->toDateString() . " 00:00:00";
                $toDate = Carbon::now('America/New_York')->toDateTimeString();

                $call_records = $call_records->whereBetween('call_time', [$fromDate,$toDate])->orderBy('call_time');
            //only end date is defined and checked. start_date is assumed to be the time the request is sent.
            } else if ($end_date_key !== FALSE && $end_date_key >= 0){

                $fromDate = Carbon::now('America/New_York')->toDateTimeString();
                $toDate = Carbon::parse($request['end_date'], 'America/New_York')->toDateString() . " 23:59:59";

                $call_records = $call_records->whereBetween('call_time', [$fromDate,$toDate])->orderBy('call_time');
            }
            //priorities json attribute is checked
            if ($priorities_key !== FALSE && $priorities_key >= 0){
                if (count($request['priorities']) > 0){
                     $call_records = $call_records->whereIn('priority', $request['priorities']);
                }
            }
            
            //districts json attribute is checked
            if ($districts_key !== FALSE && $districts_key >= 0){
                if (count($request['districts']) > 0){
                    $call_records = $call_records->whereIn('district', $request['districts']);
                }
            }            

            //DB::enableQueryLog();

            $query_result = $call_records->get(); //query is run

            //clean call records. latitude and longitude can be 0.

            $i = 0;
            $to_ret = [];
            foreach ($query_result as $value){
                if (is_string($value->bpd_call_id) && 
                    (is_numeric($value->latitude) && $value->latitude != 0) && 
                    (is_numeric($value->longitude) && $value->longitude != 0))
                {

                    array_push($to_ret, $value);
                }
            }

            $collection_to_ret = Collection::make($to_ret);

            // //print_r("after:".$query_result->count());

            //$queries = DB::getQueryLog();
            //var_dump($queries);

            return BMResponse::success($collection_to_ret);
        });

        $api->get('/count/all', function(){

            //today
            $from = Carbon::now('America/New_York')->toDateString() . " 00:00:00";
            $to = Carbon::now('America/New_York')->toDateTimeString();
            $today_count = Call::whereBetween('call_time', [$from,$to])->count();

            //week
            $from = Carbon::now('America/New_York')->startOfWeek();
            $to = Carbon::now('America/New_York')->toDateTimeString();
            $week_count = Call::whereBetween('call_time', [$from,$to])->count();
            
            //month
            $from = Carbon::now('America/New_York')->startOfMonth();
            $to = Carbon::now('America/New_York')->toDateTimeString();
            $month_count = Call::whereBetween('call_time', [$from,$to])->count();

            //year
            $from = Carbon::now('America/New_York')->startOfYear();
            $to = Carbon::now('America/New_York')->toDateTimeString();
            $year_count = Call::whereBetween('call_time', [$from,$to])->count();

            $query_return = array(
                'today' => $today_count, 
                'week' => $week_count,
                'month' => $month_count,
                'year' => $year_count
            );

            return BMResponse::success($query_return); 
        });

        $api->get('/count/today', function(){

            $from = Carbon::now('America/New_York')->toDateString() . " 00:00:00";
            $to = Carbon::now('America/New_York')->toDateTimeString();

            $query_return = Call::whereBetween('call_time', [$from,$to])->count();

            return BMResponse::success($query_return); 
        });

        $api->get('/count/week', function(){

            $from = Carbon::now('America/New_York')->startOfWeek();
            $to = Carbon::now('America/New_York')->toDateTimeString();

            $query_return = Call::whereBetween('call_time', [$from,$to])->count();

            return BMResponse::success($query_return); 
        });

        $api->get('/count/month', function(){
            $from = Carbon::now('America/New_York')->startOfMonth();
            $to = Carbon::now('America/New_York')->toDateTimeString();

            $query_return = Call::whereBetween('call_time', [$from,$to])->count();

            return BMResponse::success($query_return); 
        });

        $api->get('/count/year', function(){
            $from = Carbon::now('America/New_York')->startOfYear();
            $to = Carbon::now('America/New_York')->toDateTimeString();

            $query_return = Call::whereBetween('call_time', [$from,$to])->count();

            return BMResponse::success($query_return);
        });


    });

});
