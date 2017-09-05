<?php


namespace App\Telegram\Events;

use App\Telegram\Config;
use App\Telegram\Handlers\CodeHandler;
use App\Telegram\Handlers\ConfigHandler;
use App\Telegram\Interfaces\BotEvent;
use TelegramBot\Api\Types\Update;

class ConfigEvent implements BotEvent
{
    public static function validator(): \Closure
    {
        return function (Update $update) {
            $message = $update->getMessage();
            if (!is_null($message)) {
                list($state) = explode(':', Config::getState($message->getChat()->getId())?:'');
                return $state === 'input';
            }
            return false;
        };
    }

    public static function handle()
    {
        $handler = new ConfigHandler();
        return $handler();
    }
}
