<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 29.05.2016
 * Time: 0:33.
 */

namespace App\Games\Engines;

use App\Exceptions\TelegramCommandException;
use App\Games\BaseEngine\AbstractGameEngine;
use App\Games\Interfaces\IncludeHints;
use App\Games\Interfaces\PinEngine;
use App\Games\Sender;
use App\Helpers\Guzzle\Middleware\EncodingMiddleware;
use App\Telegram\Config;
use Illuminate\Support\Collection;

class ProbegEngine extends AbstractGameEngine implements PinEngine
{
    public function __construct($chatId)
    {
        parent::__construct($chatId);
        $this->stack->push(new EncodingMiddleware());
    }

    public function sendCode($code)
    {
        return 'отправка кода';
    }

    public function sendSpoiler($spoiler)
    {
        return 'отправка спойлера';
    }

    public function getQuestText()
    {
        return 'получение текста';
    }

    public function getQuestList()
    {
        return 'получение spisok';
    }

    public function getEstimatedCodes()
    {
        return 'getCodes';
    }

    private function getHtml()
    {
        return 111;
    }
}
