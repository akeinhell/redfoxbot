<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
 * @method static \Illuminate\Database\Query\Builder|\App\TUser whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TUser whereFirstName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TUser whereLastName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TUser whereUsername($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TUser whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TUser extends Model
{
    protected $fillable = ['id', 'first_name', 'last_name', 'username'];
}
