<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 08 Jan 2018 04:12:26 +0000.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class PasswordReset
 * 
 * @property int $id
 * @property string $email
 * @property string $token
 * @property \Carbon\Carbon $created_at
 *
 * @package App\Models\Base
 */
class PasswordReset extends Eloquent
{
	use \Reliese\Database\Eloquent\BitBooleans;
	public $timestamps = false;
}
