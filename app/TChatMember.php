<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TChatMember.
 *
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static \Illuminate\Database\Query\Builder|\App\TChatMember whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TChatMember whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TChatMember whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TChatMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TChatMember whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TChatMember extends Model
{
}
