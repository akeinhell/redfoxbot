<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
 * @method static \Illuminate\Database\Query\Builder|\App\TChat whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TChat whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TChat whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TChat whereConfig($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TChat whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\TChat whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TChat extends Model
{
    protected $fillable = ['id'];
}
