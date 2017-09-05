<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * App\TUser.
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static Builder|TUser whereId($value)
 * @method static Builder|TUser whereFirstName($value)
 * @method static Builder|TUser whereLastName($value)
 * @method static Builder|TUser whereUsername($value)
 * @method static Builder|TUser whereCreatedAt($value)
 * @method static Builder|TUser whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TUser extends Model
{
    protected $fillable = ['id', 'first_name', 'last_name', 'username'];
}
