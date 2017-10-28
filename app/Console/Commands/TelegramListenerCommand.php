<?php

namespace App\Console\Commands;

use App\Exceptions\NoQuestSelectedException;
use App\Exceptions\TelegramCommandException;
use App\Helpers\Guzzle\Exceptions\NotAuthenticatedException;
use App\Telegram\Bot;
use Illuminate\Console\Command;
use Log;
use Monolog\Handler\StdoutHandler;

class TelegramListenerCommand extends Command
{
    const CACHE_KEY = 'LAST_MESSAGE_ID';
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
    protected $description = 'start bot in ';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::getMonolog()->pushHandler(new StdoutHandler());
        $bot = Bot::getClient();

        $offset = \Cache::get(self::CACHE_KEY) ?: 0;
        \Log::info('start listen: offset = ' . $offset);

        $this->info(Bot::action()->getMe()->getUsername());
        while (true) {
            $updates = Bot::action()->getUpdates($offset, 5, 30);
            if (!$updates) {
                continue;
            }

            Log::debug('handled new updates: ' . count($updates));
            $lastUpdate = end($updates);
            $offset     = $lastUpdate->getUpdateId() + 1;
            \Cache::put(self::CACHE_KEY, $offset);
            try {
                $bot->handle($updates);
            } catch (NoQuestSelectedException|NotAuthenticatedException|TelegramCommandException $e) {
                Bot::sendMessage($e->getChatid(), $e->getMessage());
            } catch (\Exception $e) {
                Log::critical(get_class($e) . implode(PHP_EOL, [
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ]));
                app('sentry')->captureException($e);
            }
        }
    }
}
