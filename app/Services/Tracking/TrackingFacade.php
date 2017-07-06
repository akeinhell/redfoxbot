<?php

namespace App\Services\Tracking;


use Illuminate\Support\Facades\Facade;

class TrackingFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'track';
    }
}
