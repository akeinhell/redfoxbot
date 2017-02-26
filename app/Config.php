<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Config
 *
 * @property integer $id
 * @property integer $chat_id
 * @property mixed $config
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Config whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Config whereChatId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Config whereConfig($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Config whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Config whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Config extends Model
{
    protected $fillable = ['chat_id'];
}
