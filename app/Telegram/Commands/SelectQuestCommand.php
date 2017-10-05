<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Exceptions\TelegramCommandException;
use App\Telegram\AbstractCommand;
use App\Telegram\Bot;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class SelectQuestCommand extends AbstractCommand
{
    public static $description = 'Выбор задания';

    public static $entities = ['/select'];
    protected     $active   = true;
    protected     $visible  = false;
    protected     $patterns = [
        '\/select',
    ];

    public function execute($payload)
    {
        $questList = $this->getEngine()->getQuestList() ?: [];

        try {
            list($response) = $this->getEngine()->getQuestText();
            $codes    = $this->getEngine()->getEstimatedCodes();
            $response = implode(PHP_EOL, [$response, $codes]);
        } catch (TelegramCommandException $e) {
            $response = 'Выберите задание из списка';
        }

        if (!$questList) {
            return $this->responseReply = 'Не найдено списка заданий';
        }

        $data = collect($questList)->map(function ($k, $v) {
            return Bot::Button($k, ['config', 'level', $v, 'null']);
        })->values()->toArray();

        $this->responseKeyboard = new InlineKeyboardMarkup(array_chunk($data, 2));
        $this->responseText     = $response;
    }
}
