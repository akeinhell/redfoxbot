<?php

namespace App;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\User.
 *
 * @mixin \Eloquent
 *
 * @property int        $id
 * @property string         $name
 * @property string         $first_name
 * @property string         $last_name
 * @property string         $nickname
 * @property string         $email
 * @property string         $photo
 * @property string         $access_token
 * @property string         $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User whereNickname($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User wherePhoto($value)
 * @method static Builder|User whereAccessToken($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereUpdatedAt($value)
 */
class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['first_name', 'last_name', 'nickname', 'photo'];
}
