<?php

namespace App\Console\Commands;

use AmazonSNS;
use App\Jobs\AlertJob;
use AWS;
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
        publish_to_sns([
            'quest_list' => [1, 2, 3, 4]
        ]);
    }
}
