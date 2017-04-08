<?php

namespace App\Services\Lampa;


use Illuminate\Support\Facades\Facade;

class LampaFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'lampa';
    }
}
