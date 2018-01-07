<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 07 Jan 2018 22:31:53 +0000.
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
