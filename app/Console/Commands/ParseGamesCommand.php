<?php

namespace App\Console\Commands;

use App\Jobs\ParseEnconterGames;
use Illuminate\Console\Command;

class ParseGamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'parse all gamesin all engines';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        dump(dispatch(new ParseEnconterGames()));
        dump(dispatch(new ParseEnconterGames(1, [], true)));
    }
}
