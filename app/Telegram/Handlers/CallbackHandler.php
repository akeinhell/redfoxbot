<?php


namespace App\Telegram\Handlers;

use App\Telegram\Bot;
use App\Telegram\Interfaces\CallbackInterface;
use TelegramBot\Api\Types\CallbackQuery;

class CallbackHandler extends BaseHandler
{
    private $callbackQuery;

    public function run(CallbackQuery $callbackQuery)
    {
        $chatId = Bot::getChatIdfromCallback($callbackQuery);
        Bot::action()->sendChatAction($chatId, 'typing');
        $this->callbackQuery = $callbackQuery;
        $callbackData        = $this->callbackQuery->getData();
        list($action, $data) = array_pad(explode(':', $callbackData), 3, '');
        $className    = sprintf('App\Telegram\Handlers\%sCallbackHandler', ucfirst($action));
        if ($action && class_exists($className)) {
            /** @var CallbackInterface $handler */
            $handler = new $className();
            $method  = method_exists($handler, $data) ? $data : 'run';

            return call_user_func_array([$handler, $method], [$this->callbackQuery]);
        }

        $messageId = $this->callbackQuery->getMessage()->getMessageId();
        $msg       = sprintf('Действие не найдено (%s) [%s]', $action, $callbackData);

        return Bot::action()->editMessageText($chatId, $messageId, $msg);
    }
}
