<?php


namespace App\Telegram\Events;


use App\Telegram\Bot;
use App\Telegram\Handlers\CallbackHandler;
use App\Telegram\Interfaces\BotEvent;
use TelegramBot\Api\Types\Update;

class CallbackEvent implements BotEvent
{

    public static function validator(): \Closure
    {
        return function (Update $update) {
          return $update->getCallbackQuery() !== null;
        };
    }

    public static function handle()
    {
        $handler = new CallbackHandler();
        return $handler();
    }
}