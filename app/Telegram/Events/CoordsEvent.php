<?php


namespace App\Telegram\Events;


use App\Telegram\Handlers\CoordsHandler;
use App\Telegram\Interfaces\BotEvent;
use TelegramBot\Api\Types\Update;

class CoordsEvent implements BotEvent
{

    public static function validator(): \Closure
    {
        return function (Update $update) {
            $message = $update->getMessage();
            if (is_null($message)) {
                return false;
            }

            return getCoordinates($update->getMessage()->getText()) !== null;
        };
    }

    public static function handle()
    {
        $handler = new CoordsHandler();
        return $handler();
    }
}