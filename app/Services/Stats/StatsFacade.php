<?php

namespace App\Services\Stats;


use Illuminate\Support\Facades\Facade;

class StatsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'stats';
    }
}
