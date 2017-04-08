<?php

namespace App\Providers;

use App\Services\Lampa\LampaService;
use Illuminate\Support\ServiceProvider;


class LampaProvider extends ServiceProvider
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
        $this->app->singleton(LampaService::class, function() {
            return new LampaService();
        });

        $this->app->alias(LampaService::class, 'lampa');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['lampa', LampaService::class];
    }
}
