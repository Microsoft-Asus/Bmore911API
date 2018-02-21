<?php

namespace App\Helpers;

class BMResponse {

    public static function success($message){
        return response()->json(['status' => 'success', 'data' => $message], 200);
    }

    public static function bad_request($message){
        return response()->json(['status' => 'failed', 'data' => $message], 400);
    }

    public static function unauthorized($message){
        return response()->json(['status' => 'failed', 'data' => $message], 401);
    }

    public static function forbidden($message) {
        return response()->json(['status' => 'failed', 'data' => $message], 403);
    }

    public static function not_found($message){
        return response()->json(['status' => 'failed', 'data' => $message], 404);
    }

    public static function conflict($message){
        return response()->json(['status' => 'failed', 'data' => $message], 409);
    }

    public static function internal_error(){
        return response()->json(['status' => 'failed', 'data' => "An unknown error occurred."], 500);
    }

}