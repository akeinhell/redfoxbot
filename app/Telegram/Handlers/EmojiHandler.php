<?php


namespace App\Telegram\Handlers;

use App\Telegram\Bot;
use App\Telegram\Commands\CodeCommand;
use App\Telegram\Config;
use Log;
use TelegramBot\Api\Types\Update;

class EmojiHandler extends BaseHandler
{
    public function run(Update $update)
    {
        Log::info('EmojiHandler');
        $chatId = $update->getMessage()->getChat()->getId();
        $text   = $update->getMessage()->getText();
        $key    = Bot::getEmoji($text);
        $engine = Bot::getEngineFromChatId($chatId);

        if (!$key || !$engine) {
            return false;
        }

        $method = 'get' . ucfirst(Bot::$map[$key]);
        if (method_exists($engine, $method)) {
            $response = call_user_func([$engine, $method]);
            $keyboard = Bot::getKeyboard($chatId);
            Bot::sendMessage($chatId, $response, $keyboard);
        }
    }
}
