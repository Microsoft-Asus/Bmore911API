<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 08 Jan 2018 04:12:26 +0000.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class CallRecordFile
 * 
 * @property int $id
 * @property string $uri
 * @property int $last_processed_line
 * @property string $last_processed_bpd_call_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @package App\Models\Base
 */
class CallRecordFile extends Eloquent
{
	use \Reliese\Database\Eloquent\BitBooleans;

	protected $casts = [
		'last_processed_line' => 'int'
	];
}
