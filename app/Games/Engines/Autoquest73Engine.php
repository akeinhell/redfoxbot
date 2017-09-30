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
use App\Helpers\Guzzle\Middleware\EncodingMiddleware;
use App\Helpers\Guzzle\Middleware\ProbegMiddleware;
use App\Telegram\Bot;
use DOMElement;
use Symfony\Component\DomCrawler\Crawler;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class Autoquest73Engine extends AbstractGameEngine implements PinEngine
{
    private const MAIN_URL = '/go/index.php';

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
        $crawler = $this->getCrawler();

        $html = $crawler->filter('body')->each(function (Crawler $nodes) {
            $response = [];
            /** @var DOMElement $node */
            foreach ($nodes as $node) {
                $allow = [
                    'font',
                    'b',
                    'strong',
                    'font',
                    'span',
                    'div',
                ];
                $extra = [
                    'img',
                    'a',
                ];
                /** @var DOMElement|\DOMText $childNode */
                foreach ($node->childNodes as $childNode) {
                    $type = get_class($childNode);
                    $tagName = property_exists($childNode, 'tagName')?$childNode->tagName: null;
                    $text = trim($childNode->textContent);
                    if (in_array($tagName, $allow) || $type === 'DOMText') {
                        if ($text) {
                            $response[] = $text;
                        }
                    }

                    if (in_array($tagName, $extra)) {
                        $html = $childNode->ownerDocument->saveHTML($childNode);
                        $response[] = $html;
                    }
                };
            }
            return $response;
        });

        $response = collect($html)->first();

        return [implode(PHP_EOL, $response), null];
    }

    /**
     * @param string|null $html
     * @return Crawler
     */
    private function getCrawler($html = null)
    {
        $response = $html?: (string)$this->client->get(self::MAIN_URL)->getBody();
        return new Crawler($response);
    }

    public function getQuestList()
    {
        return [];
    }

    public function getEstimatedCodes()
    {
        // TODO: Implement getEstimatedCodes() method.
    }
}
