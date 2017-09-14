<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * App\TChat.
 *
 * @property int $id
 * @property string $type
 * @property string $title
 * @property mixed $config
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static Builder|TChat whereId($value)
 * @method static Builder|TChat whereType($value)
 * @method static Builder|TChat whereTitle($value)
 * @method static Builder|TChat whereConfig($value)
 * @method static Builder|TChat whereCreatedAt($value)
 * @method static Builder|TChat whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TChat extends Model
{
    protected $fillable = ['id'];
}
