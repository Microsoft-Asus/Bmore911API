<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 07 Jan 2018 22:31:53 +0000.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Job
 * 
 * @property int $id
 * @property string $queue
 * @property string $payload
 * @property int $attempts
 * @property int $reserved_at
 * @property int $available_at
 * @property int $created_at
 *
 * @package App\Models\Base
 */
class Job extends Eloquent
{
	use \Reliese\Database\Eloquent\BitBooleans;
	public $timestamps = false;

	protected $casts = [
		'attempts' => 'int',
		'reserved_at' => 'int',
		'available_at' => 'int',
		'created_at' => 'int'
	];
}
