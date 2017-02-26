<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Quest.
 *
 * @mixin \Eloquent
 *
 * @property int $id
 * @property string $key
 * @property string $title
 * @property string $link
 * @property string $placement
 * @property string $description
 * @property string $start
 * @property string $stop
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static \Illuminate\Database\Query\Builder|\App\Quest whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Quest whereKey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Quest whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Quest whereLink($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Quest wherePlacement($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Quest whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Quest whereStart($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Quest whereStop($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Quest whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Quest whereUpdatedAt($value)
 *
 * @property int $game_id
 * @property string $html_link
 * @property string $event_id
 *
 * @method static \Illuminate\Database\Query\Builder|\App\Quest whereGameId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Quest whereHtmlLink($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Quest whereEventId($value)
 */
class Quest extends Model
{
    protected $guarded = ['created_at', 'updated_at'];
}
