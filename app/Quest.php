<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

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
 * @method static Builder|Quest whereId($value)
 * @method static Builder|Quest whereKey($value)
 * @method static Builder|Quest whereTitle($value)
 * @method static Builder|Quest whereLink($value)
 * @method static Builder|Quest wherePlacement($value)
 * @method static Builder|Quest whereDescription($value)
 * @method static Builder|Quest whereStart($value)
 * @method static Builder|Quest whereStop($value)
 * @method static Builder|Quest whereCreatedAt($value)
 * @method static Builder|Quest whereUpdatedAt($value)
 *
 * @property int $game_id
 * @property string $html_link
 * @property string $event_id
 *
 * @method static Builder|Quest whereGameId($value)
 * @method static Builder|Quest whereHtmlLink($value)
 * @method static Builder|Quest whereEventId($value)
 */
class Quest extends Model
{
    protected $guarded = ['created_at', 'updated_at'];
}
