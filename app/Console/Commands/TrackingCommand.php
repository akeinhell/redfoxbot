<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class TrackingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'tracking:run';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        var_dump(\Redis::sadd(TRACKING_KEY, [1,2,3,4,5]));
        var_dump('chats', \Redis::smembers(TRACKING_KEY));


    }
}
