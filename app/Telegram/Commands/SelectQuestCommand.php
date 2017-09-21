<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Telegram\AbstractCommand;
use App\Telegram\Bot;
use App\Telegram\Config;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
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
        $questList = $this->getEngine()->getQuestList()?:[];

        if (!$questList) {
            return $this->responseReply = 'Не найдено списка заданий';
        }

        $data = collect($questList)->map(function ($v, $k) {
            return Bot::Button($k, ['config', 'level', $v, $k]);
        })->values()->toArray();

        $this->responseKeyboard = new InlineKeyboardMarkup(array_chunk($data, 2));
        $this->responseText     = 'Выберите задание из списка';
    }
}
