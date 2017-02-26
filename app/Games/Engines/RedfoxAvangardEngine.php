<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 11.04.16
 * Time: 12:56.
 */

namespace App\Games\Engines;

use App\Exceptions\TelegramCommandException;
use App\Games\BaseEngine\RedfoxBaseEngine;

class RedfoxAvangardEngine extends RedfoxBaseEngine
{
    public function sendSpoiler($text)
    {
        return parent::sendSpoiler($text);
    }

    public function getQuestList()
    {
        throw new TelegramCommandException('У авангарда не предусмотрено получение списка заданий', __LINE__);
    }

    protected function getUrl($type)
    {
        $url = null;
        switch ($type) {
            case self::CODE_URL:
                $url = '/play/submit';
                break;
            case self::QUEST_URL:
                $url = '/play/';
                break;
            case self::SPOILER_URL:
                $url = '/play/submitspoiler';
                break;
            case self::QUEST_LIST_URL:
                $url = '/play/safari';
                break;
            default:
                throw new \Exception('Не опознанная ошибка.', __LINE__);
        }

        return $url;
    }
}
