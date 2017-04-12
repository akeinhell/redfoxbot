<?php

namespace App\Services\Redfoxbot;

use Illuminate\Support\Facades\Facade;

class RedfoxbotFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'redfoxbot';
    }
}
