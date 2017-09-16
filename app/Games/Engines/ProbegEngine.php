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
use App\Telegram\Config;
use Illuminate\Support\Collection;

class ProbegEngine extends AbstractGameEngine implements PinEngine
{
    public function sendCode($code)
    {
        // TODO: Implement sendCode() method.
    }

    public function sendSpoiler($spoiler)
    {
        // TODO: Implement sendSpoiler() method.
    }

    public function getQuestText()
    {
        // TODO: Implement getQuestText() method.
    }

    public function getQuestList()
    {
        // TODO: Implement getQuestList() method.
    }

    public function getEstimatedCodes()
    {
        // TODO: Implement getEstimatedCodes() method.
    }
}
