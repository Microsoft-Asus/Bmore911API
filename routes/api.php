<?php

use Dingo\Api\Routing\Router;
use App\Helpers\BMResponse;
use App\Helpers\BMValidate;
use App\Models\Call;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
            'message' => 'This is a simple example of item. Everyone can see it.'
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
                return BMResponse::bad_request('No call record parameters specified');
            }

            $start_date_key = array_search('start_date', $params_present);
            $end_date_key = array_search('end_date', $params_present);
            $priorities_key = array_search('priorities', $params_present);
            $districts_key = array_search('districts', $params_present);

            if ( ($start_date_key !== FALSE && $start_date_key >= 0) && ($end_date_key !== FALSE && $end_date_key >= 0)){

                $fromDate = Carbon::createFromFormat('Y-m-d', $request[$params_present[$start_date_key]],'America/New_York')->toDateTimeString() . " 00:00:00";
                $toDate = Carbon::createFromFormat('Y-m-d', $request[$params_present[$end_date_key]], 'America/New_York')->toDateTimeString() . " 23:59:59";

                $call_records = $call_records->whereBetween('call_time', [$fromDate,$toDate])->orderBy('call_time');

            } else if ($start_date_key !== FALSE && $start_date_key >= 0){

                $fromDate = Carbon::createFromFormat('Y-m-d', $request[$params_present[$start_date_key]],'America/New_York')->toDateTimeString() . " 00:00:00";
                $toDate = Carbon::now()->toDateTimeString() . " 23:59:59";

                $call_records = $call_records->whereBetween('call_time', [$fromDate,$toDate])->orderBy('call_time');
            } else if ($end_date_key !== FALSE && $end_date_key >= 0){
                $fromDate = Carbon::now()->toDateTimeString() . " 00:00:00";
                $toDate = Carbon::createFromFormat('Y-m-d', $request[$params_present[$end_date_key]],'America/New_York')->toDateTimeString() . " 23:59:59";

                $call_records = $call_records->whereBetween('call_time', [$fromDate,$toDate])->orderBy('call_time');
            }

            if ($priorities_key !== FALSE && $priorities_key >= 0){
                $call_records = $call_records->where('priority', '=', $request['priorities'], 'and');
            }

            if ($districts_key !== FALSE && $districts_key >= 0){
                $call_records = $call_records->where('district', '=', $request['districts'], 'and');
            }

            $result = $call_records->get();

            return BMResponse::success($result);
        });
    });

});
