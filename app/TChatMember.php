<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * App\TChatMember.
 *
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static Builder|TChatMember whereId($value)
 * @method static Builder|TChatMember whereUserId($value)
 * @method static Builder|TChatMember whereStatus($value)
 * @method static Builder|TChatMember whereCreatedAt($value)
 * @method static Builder|TChatMember whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TChatMember extends Model
{
}
