<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class EnvVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:env';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update env version';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $process = new Process('git describe --abbrev=0 --tags');
        $process->run();

        $version = trim($process->getOutput());
        $env = file_get_contents('.env');
        $lines = collect(explode(PHP_EOL, $env))
            ->filter(function($item){
                return $item;
            })
            ->map(function($item) use ($version){
                $v = explode('=', $item, 2);
                if ($v[0] == 'BOT_VERSION'){
                    $v[1] = $version;
                }
                return implode('=', $v);
            });

        file_put_contents('.env', implode(PHP_EOL, $lines->toArray()));
    }
}
