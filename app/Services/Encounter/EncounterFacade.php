<?php

namespace App\Services\Encounter;


use Illuminate\Support\Facades\Facade;

class EncounterFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'encounter';
    }
}
