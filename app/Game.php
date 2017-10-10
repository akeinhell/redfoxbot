<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $guarded = ['id'];
    public function city() {
        return $this->belongsTo(City::class);
    }
}
