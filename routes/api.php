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
    $api->group(['prefix' => 'auth'], function(Router $api) {
        $api->post('signup', 'App\\Api\\V1\\Controllers\\SignUpController@signUp');
        $api->post('login', 'App\\Api\\V1\\Controllers\\LoginController@login');

        $api->post('recovery', 'App\\Api\\V1\\Controllers\\ForgotPasswordController@sendResetEmail');
        $api->post('reset', 'App\\Api\\V1\\Controllers\\ResetPasswordController@resetPassword');

        $api->post('logout', 'App\\Api\\V1\\Controllers\\LogoutController@logout');
        $api->post('refresh', 'App\\Api\\V1\\Controllers\\RefreshController@refresh');
        $api->get('me', 'App\\Api\\V1\\Controllers\\UserController@me');
    });

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

    $api->group(['prefix' => 'records'], function(Router $api) {
        $api->post('/search', function(Request $request){

            $call_records = Call::query();
            $fromDate = "";
            $toDate = "";

            $validator = BMValidate::init($request);
            $params_present = $validator->check(['start_date', 'end_date', 'priorities', 'districts'])->getParamsPresent();

            if (count($params_present) == 0){
                return BMResponse::bad_request('Query missing parameters. Required: start_date, end_date | Optional: priorities[], districts[]');
            }

            $start_date_key = array_search('start_date', $params_present);
            $end_date_key = array_search('end_date', $params_present);
            $priorities_key = array_search('priorities', $params_present);
            $districts_key = array_search('districts', $params_present);

            if ( ($start_date_key !== FALSE && $start_date_key >= 0) && ($end_date_key !== FALSE && $end_date_key >= 0)){

                $fromDate = Carbon::parse($request['start_date'])->toDateString() . " 00:00:00";
                $toDate = Carbon::parse($request['end_date'])->toDateString() . " 23:59:59";

                $call_records = $call_records->whereBetween('call_time', [$fromDate,$toDate])->orderBy('call_time');

            } else if ($start_date_key !== FALSE && $start_date_key >= 0){

                $fromDate = Carbon::parse($request['start_date'])->toDateString() . " 00:00:00";
                $toDate = Carbon::now('America/New_York')->toDateTimeString();

                $call_records = $call_records->whereBetween('call_time', [$fromDate,$toDate])->orderBy('call_time');

            } else if ($end_date_key !== FALSE && $end_date_key >= 0){

                $fromDate = Carbon::now('America/New_York')->toDateTimeString();
                $toDate = Carbon::parse($request['end_date'])->toDateString() . " 23:59:59";

                $call_records = $call_records->whereBetween('call_time', [$fromDate,$toDate])->orderBy('call_time');
            }

            if ($priorities_key !== FALSE && $priorities_key >= 0){
                if (count($request['priorities']) > 0){
                     $call_records = $call_records->whereIn('priority', $request['priorities']);
                }
            }
            

            if ($districts_key !== FALSE && $districts_key >= 0){
                if (count($request['districts']) > 0){
                    $call_records = $call_records->whereIn('district', $request['districts']);
                }
            }            

            //DB::enableQueryLog();

            $query_result = $call_records->get();

            //clean call records

            //print_r("before:".$query_result->count());
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

            // //$queries = DB::getQueryLog();
            // //var_dump($queries);

            return BMResponse::success($collection_to_ret);
        });

        $api->get('/count/day', function(){

            $from = Carbon::now()->toDateString() . " 00:00:00";
            $to = Carbon::now()->toDateTimeString();

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

        $api->get('/count/total', function(){
            $query_return = Call::count();

            return BMResponse::success($query_return);
        });


    });

});
