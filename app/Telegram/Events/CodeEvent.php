<?php


namespace App\Telegram\Events;


use App\Telegram\Config;
use App\Telegram\Handlers\CodeHandler;
use App\Telegram\Interfaces\BotEvent;
use TelegramBot\Api\Types\Update;

class CodeEvent implements BotEvent
{

    public static function validator(): \Closure
    {
        return function (Update $update) {
            $message = $update->getMessage();
            if (is_null($message)) {
                return false;
            }
            $chatId = $update->getMessage()->getChat()->getId();
            $auto = filter_var(Config::getValue($chatId, 'auto', 'true'), FILTER_VALIDATE_BOOLEAN);
            $pattern = Config::getValue($chatId, 'format');

            return $pattern && (preg_match('/^[' . $pattern . ']+$/i', $message->getText()) && $auto) || preg_match('/^!(.*?)$/i', $message->getText());
        };
    }

    public static function handle()
    {
        $handler = new CodeHandler();
        return $handler();
    }
}