<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\EnvVersion::class,
        Commands\TrackingCommand::class,
        Commands\TestAlerts::class,
        Commands\TelegramListenerCommand::class,
        Commands\ParseGamesCommand::class
    ];
}
