<?php

namespace App\Providers;

use App\Services\Tracking\TrackingService;
use Illuminate\Support\ServiceProvider;


class TrackProvider extends ServiceProvider
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
        $this->app->singleton(TrackingService::class, function() {
            return new TrackingService();
        });

        $this->app->alias(TrackingService::class, 'track');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['track', TrackingService::class];
    }
}
