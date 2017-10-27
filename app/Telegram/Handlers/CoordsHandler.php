<?php


namespace App\Telegram\Handlers;

use App\Telegram\Bot;
use TelegramBot\Api\Types\Update;

class CoordsHandler extends BaseHandler
{
    public function run(Update $update)
    {
        $message = $update->getMessage();
        $chatId  = $message->getChat()->getId();
        Bot::detectCoords($chatId, $message->getText());

    }
}
