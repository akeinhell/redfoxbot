<?php

namespace App\Providers;

use App\Services\Redfoxbot\RedfoxbotService;
use Illuminate\Support\ServiceProvider;

class RedfoxbotProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Redfoxbot', function($app) {
            return new RedfoxbotService();
        });
        $this->app->alias(RedfoxbotService::class, 'redfoxbot');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['redfoxbot'];
    }
}
