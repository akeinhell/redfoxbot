<?php


namespace App\Telegram\Handlers;


use App\Telegram\Bot;
use App\Telegram\Commands\CodeCommand;
use TelegramBot\Api\Types\Update;

class CodeHandler extends BaseHandler
{
    public function run(Update $update) {
        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $command = new CodeCommand($chatId);
        $command->execute(ltrim($message->getText(), '!'));
        if ($command->getResponseText()) {
            $reply = $command->getResponseReply() ? $message->getMessageId() : null;
            Bot::sendMessage($chatId, $command->getResponseText(), $command->getResponseKeyboard(), $reply);
        }
    }
}