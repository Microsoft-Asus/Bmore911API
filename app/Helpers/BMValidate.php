<?php
/**
 * Created by PhpStorm.
 * User: aballiu
 * Date: 1/11/17
 * Time: 9:05 PM
 */

namespace App\Helpers;

use Illuminate\Http\Request;

class BMValidate {

    public $json_data;
    public $params_missing;
    public $params_present;

    public static function init(Request $request){
        $instance = new BMValidate();
        if ($request->getContentType() == 'json'){
            $json_data = json_decode($request->getContent(), true);
            $instance->json_data = $json_data;
        }
        return $instance;
    }

    public function getJSONData() {
        return $this->json_data;
    }

    public function getParamsPresent() {
        return $this->params_present;
    }

    public function check($params){

        $params_missing = array();
        $params_present = array();

        $count1 = 0;
        $count2 = 0;
        foreach($params as $key => $val){
            if (!array_has($this->json_data, $val)){
                //$params_missing[$count1] = $val;
                array_push($params_missing, $val);
                $count1++;
            } else {
                if (empty($this->json_data[$val])){ //making sure its not empty
                    array_push($params_missing, $val);
                    $count1++;
                } else {
                    array_push($params_present, $val);
                    $count2++;
                }
            }
        }

        $this->params_present = $params_present;
        $this->params_missing = $params_missing;

        return $this;
    }

    public function serialize($array){
        return implode(", ", $array);
    }

}