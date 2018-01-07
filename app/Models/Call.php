<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 29 Dec 2017 03:15:10 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Call
 * 
 * @property string $bpd_call_id
 * @property \Carbon\Carbon $call_time
 * @property int $priority
 * @property string $district
 * @property string $description
 * @property string $address
 * @property float $latitude
 * @property float $longitude
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @package App\Models
 */
class Call extends Eloquent
{

	public static $PRIORITY_NON_EMERGENCY = 0;
	public static $PRIORITY_LOW = 1;
	public static $PRIORITY_MEDIUM = 2;
	public static $PRIORITY_HIGH = 3;
	public static $PRIORITY_UNKNOWN = 4;

	public static $STRING_PRIORITY_NON_EMERGENCY = "Non-Emergency";
	public static $STRING_PRIORITY_LOW = "Low";
	public static $STRING_PRIORITY_MEDIUM = "Medium";
	public static $STRING_PRIORITY_HIGH = "High";

	use \Reliese\Database\Eloquent\BitBooleans;
	protected $primaryKey = 'bpd_call_id';
	public $incrementing = false;

	protected $casts = [
		'priority' => 'int',
		'latitude' => 'float',
		'longitude' => 'float'
	];

	protected $dates = [
		'call_time'
	];

	protected $fillable = [
		'call_time',
		'priority',
		'district',
		'description',
		'address',
		'latitude',
		'longitude'
	];

	/**
      * @return mixed
      */
     public function getCallTime()
     {
         return $this->call_time;
     }
 
     /**
      * @param mixed $call_time
      */
     public function setCallTime($call_time)
     {
         $this->call_time = $call_time;
     }
 
     /**
      * @return mixed
      */
     public function getPriority()
     {
         return $this->priority;
     }
 
     /**
      * @param mixed $priority
      */
     public function setPriority($priority)
     {
         $this->priority = $priority;
     }
 
     /**
      * @return mixed
      */
     public function getDistrict()
     {
         return $this->district;
     }
 
     /**
      * @param mixed $district
      */
     public function setDistrict($district)
     {
         $this->district = $district;
     }
 
     /**
      * @return mixed
      */
     public function getDescription()
     {
         return $this->description;
     }
 
     /**
      * @param mixed $description
      */
     public function setDescription($description)
     {
         $this->description = $description;
     }
 
     /**
      * @return mixed
      */
     public function getBpdCallId()
     {
         return $this->bpd_call_id;
     }
 
     /**
      * @param mixed $bpd_call_id
      */
     public function setBpdCallId($bpd_call_id)
     {
         $this->bpd_call_id = $bpd_call_id;
     }
 
     /**
      * @return mixed
      */
     public function getAddress()
     {
         return $this->address;
     }
 
     /**
      * @param mixed $address
      */
     public function setAddress($address)
     {
         $this->address = $address;
     }
 
     /**
      * @return mixed
      */
     public function getLatitude()
     {
         return $this->latitude;
     }
 
     /**
      * @param mixed $latitude
      */
     public function setLatitude($latitude)
     {
         $this->latitude = $latitude;
     }
 
     /**
      * @return mixed
      */
     public function getLongitude()
     {
         return $this->longitude;
     }
 
     /**
      * @param mixed $longitude
      */
     public function setLongitude($longitude)
     {
         $this->longitude = $longitude;
     }
}
