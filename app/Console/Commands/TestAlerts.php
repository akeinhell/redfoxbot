<?php

namespace App\Console\Commands;

use App\Jobs\AlertJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TestAlerts extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test alert via sqs';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->dispatch(new AlertJob());
    }
}
