<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class ConsoleBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'start listen updates';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Start pooling bot updates');

        while (true) {
            if ($updates = Telegram::commandsHandler(false)) {
                foreach ($updates as $update) {
                    \Redfoxbot::parseUpdate($update);
                }
            }
        }
    }
}
