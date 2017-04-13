<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Telegram\AbstractCommand;
use App\Telegram\Config;
use TelegramBot\Api\Types\ReplyKeyboardHide;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class SelectQuestCommand extends AbstractCommand
{
    public static $description = 'Выбор задания';

    public static $entities = ['/select'];
    protected $active       = true;
    protected $visible      = false;
    protected $patterns     = [
        '\/select',
    ];

    public function execute($payload)
    {
        if (!empty($payload)) {
            $params                = explode(' ', $payload, 2);
            $questId               = $params[0];
            $questText             = count($params) === 2 ? $params[1] : 'Задание №' . $questId;
            $this->responseText    = sprintf('Для данного чата выбрано задание %s [%s]', $questText, $questId);
            $this->config->questId = $questId;
            Config::setValue($this->chatId, 'questId', $questId);

            $this->responseKeyboard = new ReplyKeyboardHide();

            return;
        }

        $questList = $this->getEngine()->getQuestList();
        $reply     = [];
        foreach ($questList as $id => $text) {
            $reply[][] = sprintf('/select %d [%s]', $id, $text);
        }

        $this->responseKeyboard = new ReplyKeyboardMarkup($reply, true);
        $this->responseText     = 'Выберите задание из списка';
    }
}
