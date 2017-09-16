<?php


namespace App\Telegram\Handlers;

use App\Games\Interfaces\IncludeHints;
use App\Telegram\AbstractCommand;
use App\Telegram\Bot;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\MessageEntity;

class CommandHandler extends BaseHandler
{
    private $commandClass;

    public function __construct($commandClass)
    {
        $this->commandClass = $commandClass;
    }

    public function run(Message $message)
    {
        \Log::debug('run CommandHandler: ' . $this->commandClass);
        $chatId = $message->getChat()->getId();
        /** @var AbstractCommand $command */
        $command = new $this->commandClass($chatId, $message->getFrom()->getId(), $message->getText());
        $time = microtime(true);
        $payload = $message->getText();
        collect($message->getEntities())
            ->reverse()
            ->filter(function (MessageEntity $entity) {
                return in_array($entity->getType(), ['mention', 'bot_command']);
            })
            ->each(function (MessageEntity $entity) use (&$payload) {
                $payload = substr_replace($payload, '', $entity->getOffset(), $entity->getLength() + 1);
            })
        ;

        $command->execute($payload);

        \Log::debug(sprintf('[%s] Execution time: %s', $this->commandClass, microtime(true) - $time));

        if ($command->getResponseText()) {
            $reply = $command->getResponseReply() ? $message->getMessageId() : null;
            Bot::sendMessage($chatId, $command->getResponseText(), $command->getResponseKeyboard(), $reply);
        }
    }
}
