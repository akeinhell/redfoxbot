<?php


namespace App\Telegram\Events;

use App\Telegram\Bot;
use App\Telegram\Config;
use App\Telegram\Handlers\CodeHandler;
use App\Telegram\Handlers\EmojiHandler;
use App\Telegram\Interfaces\BotEvent;
use TelegramBot\Api\Types\Update;

class EmojiEvent implements BotEvent
{
    public static function validator(): \Closure
    {
        return function (Update $update) {
            $message = $update->getMessage();

            if (is_null($message)) {
                return false;
            }

            $text    = $update->getMessage()->getText();
            if (is_null($text)) {
                return false;
            }

            return Bot::getEmoji($text) !== null;
        };
    }

    public static function handle()
    {
        $handler = new EmojiHandler();
        return $handler();
    }
}
