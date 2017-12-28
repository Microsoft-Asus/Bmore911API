<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
//+ id: 123 (number, required) - Unique autoincrementing id of the record in the table.
//+ time: `2012-04-23T18:25:43.511Z` (string, required) - The time the call was made
//+ priority: 1 (number, required) - The priority of the call (0 - non-emergency, 1 - low, 2 - medium, 3 - high).
//+ district: SW (string, required) - The district the call was made in.
//+ description: AGGRAV ASSAULT (string, required) - Description of what was happening during the call or reason for the call.
//+ bpd_call_id: P152391782 (string, required) - Baltimore Police Dept. call identification number
//+ address: 3400 BENSON AV (string,required) - Address near the call or where the call originated.
//+ latitude: 39.265965 (number, required) - Latitude coordinate the call was made from.
//+ longitude -76.650946 (number, required) - Longitude coordinate the call was made from.
//+ created_at: `2012-04-23T18:25:43.511Z` (string, required) - When this call record was created in the database.
//+ updated_at: `2012-04-23T18:25:43.511Z` (string, required) - When this call record was updated last in the database.

    public static $PRIORITY_NON_EMERGENCY = 0;
    public static $PRIORITY_LOW = 1;
    public static $PRIORITY_MEDIUM = 2;
    public static $PRIORITY_HIGH = 3;
    public static $PRIORITY_UNKNOWN = 4;

    public static $STRING_PRIORITY_NON_EMERGENCY = "Non-Emergency";
    public static $STRING_PRIORITY_LOW = "Low";
    public static $STRING_PRIORITY_MEDIUM = "Medium";
    public static $STRING_PRIORITY_HIGH = "High";

    protected $table = "calls";

    protected $primaryKey = 'bpd_call_id';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'call_time',
        'priority',
        'district',
        'description',
        'bpd_call_id',
        'address',
        'latitude',
        'longitude',
        'updated_at',
        'created_at'
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
