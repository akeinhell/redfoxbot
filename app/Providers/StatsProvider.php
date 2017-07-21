<?php

namespace App\Providers;

use App\Services\Stats\StatsService;
use Illuminate\Support\ServiceProvider;


class StatsProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(StatsService::class, function() {
            return new StatsService();
        });

        $this->app->alias(StatsService::class, 'stats');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['stats', StatsService::class];
    }
}
