<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * App\Config
 *
 * @property integer $id
 * @property integer $chat_id
 * @property mixed $config
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static Builder|Config whereId($value)
 * @method static Builder|Config whereChatId($value)
 * @method static Builder|Config whereConfig($value)
 * @method static Builder|Config whereCreatedAt($value)
 * @method static Builder|Config whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Config extends Model
{
    protected $fillable = ['chat_id'];
}
