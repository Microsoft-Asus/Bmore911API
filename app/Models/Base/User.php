<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 07 Jan 2018 22:31:53 +0000.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @package App\Models\Base
 */
class User extends Eloquent
{
	use \Reliese\Database\Eloquent\BitBooleans;
}
