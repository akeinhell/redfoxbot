<?php

namespace App\Console\Commands;

use App\Exceptions\TelegramCommandException;
use App\Telegram\Bot;
use App\Telegram\Events\CallbackEvent;
use Illuminate\Console\Command;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;

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
        $bot = Bot::getClient();

        $offset = \Cache::get(self::CACHE_KEY);
        $this->info('start listen: ' . $offset);

        while (true) {
            $updates = Bot::action()->getUpdates($offset, 5, 30);
            if ($updates) {
                $lastUpdate = end($updates);
                $offset = $lastUpdate->getUpdateId() + 1;
                \Cache::put(self::CACHE_KEY, $offset);
                try {
                    $bot->handle($updates);
                } catch (TelegramCommandException $e) {
                    Bot::sendMessage($e->getChatid(), $e->getMessage());
                    $this->warn(sprintf('#%s - %s', $e->getChatid(), $e->getMessage()));
                } catch (\Exception $e) {
                    app('sentry')->captureException($e);
                    $message = sprintf('%s (%s)'.PHP_EOL. '%s:%s',
                        get_class($e),
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    );

                    $this->error($message);
                }
            }
        }
    }
}
