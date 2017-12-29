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
}
