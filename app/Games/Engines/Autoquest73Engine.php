<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 29.05.2016
 * Time: 0:33.
 */

namespace App\Games\Engines;

use App\Games\BaseEngine\AbstractGameEngine;
use App\Games\Interfaces\PinEngine;
use App\Helpers\Guzzle\Middleware\Autoquest73Middleware;
use App\Helpers\Guzzle\Middleware\ProbegMiddleware;
use App\Telegram\Bot;
use DOMElement;
use Symfony\Component\DomCrawler\Crawler;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class Autoquest73Engine extends AbstractGameEngine implements PinEngine
{
    public function __construct($chatId)
    {
        parent::__construct($chatId);
        $this->stack->push(new Autoquest73Middleware([], $chatId));
    }

    public function sendCode($code)
    {
    }

    public function sendSpoiler($spoiler)
    {
    }

    public function getQuestText()
    {
    }

    /**
     * @param string|null $html
     * @return Crawler
     */
    private function getCrawler($html = null)
    {
        $response = $html?: (string)$this->client->get('play')->getBody();
        return new Crawler($response);
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
