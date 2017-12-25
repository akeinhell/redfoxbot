<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 07.09.17
 * Time: 21:04
 */

namespace App\Telegram\Handlers;

use App\Telegram\Bot;
use App\Telegram\Commands\ConfigCommand;
use App\Telegram\Config;
use App\Telegram\Interfaces\CallbackInterface;
use File;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class ConfigCallbackHandler implements CallbackInterface
{
    private $map = [
        'project'  => 'Ссылку на движок',
        'url'      => 'Ссылку на движок',
        'login'    => 'логин',
        'password' => 'пароль',
        'pin'      => 'pin код',
    ];

    public function run(CallbackQuery $callbackQuery)
    {
        throw new \Exception('cannot parse callback data: ' . $callbackQuery->getData());
    }

    public function level(CallbackQuery $callbackQuery)
    {
        $chatId = Bot::getChatIdfromCallback($callbackQuery);
        list(, , $levelId, $levelName) = array_pad(explode(':', $callbackQuery->getData()), 4, '');
        Config::setValue($chatId, 'level', $levelId);
        Config::setValue($chatId, 'questId', $levelId);
        $engine = Bot::getEngineFromChatId($chatId);
        $response = $engine->getQuestText();

        $response = is_array($response) ? array_pad($response, 2, null) : [$response, null];
        list($text, $keyboard) = $response;

        $codes = $engine->getEstimatedCodes();
        $text = strip_tags(implode(PHP_EOL, [
            $text, $codes, 'Выбрано задание: '. $levelName
        ]));
        if ($text == $callbackQuery->getMessage()->getText()) {
            $text .= '.';
        }
        $messageId = $callbackQuery->getMessage()->getMessageId();

        try {
           return Bot::action()->editMessageText($chatId, $messageId, $text, 'HTML', false, $keyboard);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function select(CallbackQuery $callbackQuery)
    {
        $chatId = Bot::getChatIdfromCallback($callbackQuery);
        list(, , $project) = array_pad(explode(':', $callbackQuery->getData()), 4, '');
        if (class_exists(sprintf('\App\Games\Engines\%sEngine', $project))) {
            Config::setValue($chatId, 'project', $project);

            return Bot::action()->editMessageText($chatId,
                $callbackQuery->getMessage()->getMessageId(),
                'Выбран движок ' . $project,
                null,
                false,
                ConfigCommand::getConfigKeyboard($chatId));
        };

        return Bot::action()->editMessageText($chatId,
            $callbackQuery->getMessage()->getMessageId(),
            'error: not found: ' . $project ?: 'undefined',
            null,
            false,
            ConfigCommand::getConfigKeyboard($chatId));
    }

    public function project(CallbackQuery $callbackQuery)
    {
        $chatId         = Bot::getChatIdfromCallback($callbackQuery);
        $selectedEngine = Config::getValue($chatId, 'project');
        if (in_array($selectedEngine, ['RedfoxAvangard', 'RedfoxSafari'])) {
            $keyboard = new InlineKeyboardMarkup([
                [
                    Bot::Button('Avangard', ['config', 'select', 'RedfoxAvangard']),
                    Bot::Button('Safari', ['config', 'select', 'RedfoxSafari']),
                ],
            ]);

            return Bot::action()->editMessageText($chatId, $callbackQuery->getMessage()->getMessageId(),
                'Выбери движок', null, false, $keyboard);
        }
        Bot::action()->answerCallbackQuery($callbackQuery->getId(), 'Выбор доступен только для движков Redfox');
    }

    public function input(CallbackQuery $callbackQuery)
    {
        $chatId = Bot::getChatIdfromCallback($callbackQuery);
        list(, , $key) = array_pad(explode(':', $callbackQuery->getData()), 4, '');
        $message = 'Пришлите в следующем сообщении ' . array_get($this->map, $key, $key);
        Bot::action()->editMessageText($chatId, $callbackQuery->getMessage()->getMessageId(), $message);
        Config::setState($chatId, 'input:' . $key);
    }

    public function end(CallbackQuery $callbackQuery)
    {
        $chatId = Bot::getChatIdfromCallback($callbackQuery);
        Config::setState($chatId, '');
        $cookieFile = Config::getCookieFile($chatId);
        if (File::exists($cookieFile)) {
            File::delete($cookieFile);
        }
        Bot::action()->editMessageText(
            $chatId,
            $callbackQuery->getMessage()->getMessageId(),
            'Настройка завершена'
        );
    }

    public function clean(CallbackQuery $callbackQuery)
    {
        $chatId = Bot::getChatIdfromCallback($callbackQuery);
        $cookieFile = Config::getCookieFile($chatId);
        if (File::exists($cookieFile)) {
            File::delete($cookieFile);
        }
        Config::set($chatId, new \stdClass());
        try {
            Bot::action()->editMessageText(
                $chatId,
                $callbackQuery->getMessage()->getMessageId(),
                'Настройки сброшены',
                null,
                false,
                ConfigCommand::getConfigKeyboard($chatId)
            );
        } catch (\Exception $e) {
            return false;
        }
    }
}
