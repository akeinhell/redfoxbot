<?php

namespace App\Providers;

use App\Services\Encounter\EncounterService;
use Blade;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(EncounterService::class, function () {
            return new EncounterService();
        });

        $this->app->alias(EncounterService::class, 'encounter');
    }

    public function provides()
    {
        return ['encounter'];
    }
}
