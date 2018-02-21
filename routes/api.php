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
        $api->get('/search', function(Request $request){

            $call_records = Call::query();

            $validator = BMValidate::init($request);
            $params_present = $validator->check(['start_date', 'end_date', 'priority', 'district'])->getParamsPresent();

            if (count($params_present) == 0){
                BMResponse::bad_request('No call record parameters specified');
            }
            
            if (array_has($params_present, 'start_date') && array_has($params_present, 'end_date')){

                $from = Carbon::createFromFormat('Y-m-d', $params_present['start_date'])->toDateTimeString();
                $to = Carbon::createFromFormat('Y-m-d', $params_present['end_date'])->toDateTimeString();

                $call_records = $call_records->whereBetween('call_time', array($from,$to))->orderBy('call_time');

            } else if (array_has($params_present, 'start_date')){
                $from = Carbon::createFromFormat('Y-m-d', $params_present['start_date'])->toDateTimeString();
                $to = Carbon::now()->toDateTimeString();

                $call_records = $call_records->whereBetween('call_time', array($from,$to))->orderBy('call_time');
            } else if (array_has($params_present, 'end_date')){
                $from = Carbon::now()->toDateTimeString();
                $to = Carbon::createFromFormat('Y-m-d', $params_present['end_date'])->toDateTimeString();

                $call_records = $call_records->whereBetween('call_time', array($from,$to))->orderBy('call_time');
            }

            if (array_has($params_present, 'priority')){
                $call_records = $call_records->where('priority', $params_present['priority']);
            }

            if (array_has($params_present, 'district')){
                $call_records = $call_records->where('district', $params_present['district']);
            }

            $call_records = $call_records->get();

            BMResponse::success($call_records);

        });
    });

});
