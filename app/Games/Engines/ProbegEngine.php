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
use App\Helpers\Guzzle\Middleware\ProbegMiddleware;
use App\Telegram\Bot;
use DOMElement;
use Symfony\Component\DomCrawler\Crawler;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class ProbegEngine extends AbstractGameEngine implements PinEngine
{
    public function __construct($chatId)
    {
        parent::__construct($chatId);
        $this->stack->push(new ProbegMiddleware($chatId));
//        $this->stack->push(new EncodingMiddleware());
    }

    public function sendCode($code)
    {
        $crawler = $this->getCrawler();
        $inputs = $crawler->filter('input[name=tn]')->each(function (Crawler $c) {
            return $c->attr('value');
        });

        $response = (string) $this->client->get('play', [
            'query' => [
                'tn' => array_get($inputs, 0, ''),
                'bb[]' => iconv('utf8', 'cp1251', $code),
            ],
        ])->getBody();

        $crawler = new Crawler($response);
        $result = $crawler->filter('p font')->each(function (Crawler $c) {
            return $c->parents()->text();
        });

        return implode(PHP_EOL, $result);
    }

    public function sendSpoiler($spoiler)
    {
    }

    public function getQuestText()
    {
        $crawler = $this->getCrawler();
        $questList = $this->getQuestList($crawler);
        $form = $crawler->filter('form');

        foreach (['i', 'a', 'span.drw'] as $tag) {
            $form->filter($tag)->each($this->removeChilds());
        }

        $form->filter('br')->each(function (Crawler $c) {
            foreach ($c as $node) {
                /* @var DOMElement $node */

                $node->parentNode->replaceChild(new \DOMText(PHP_EOL), $node);
                //$node->parentNode->removeChild($node);
            }
        });


        $response = trim($form->text());
        $response = trim(preg_replace('/(  )/isu', '', $response));
        $response = trim(preg_replace('/[\s]{3,}/iu', PHP_EOL, $response));
        $keyboard = $this->getInlineKeyboard($questList);

        return [$response, $keyboard];
    }

    public function getQuestList(Crawler $crawler = null)
    {
        $crawler = $crawler ?? $this->getCrawler();
        $questList = [];
        $crawler->filter('form a')
            ->each(function (Crawler $c) use (&$questList) {
                $href = trim($c->attr('href'), '?');
                $href = array_get(explode('#', $href), 0, '');
                $href = \GuzzleHttp\Psr7\parse_query($href);

                $questList[$c->text()] = (int)array_get($href, 'o', 0);
            });
        return array_filter($questList);
    }

    public function getEstimatedCodes($crawler = null)
    {
        return '';
        $crawler = $crawler ?? $this->getCrawler();

        return 'getCodes';
    }

    /**
     * @return Crawler
     */
    private function getCrawler()
    {
        $response = (string)$this->client->get('play')->getBody();
        return new Crawler($response);
    }

    private function getInlineKeyboard($questList)
    {
        if (!$questList) {
            return false;
        }

        $data = collect($questList)->map(function ($v, $k) {
            return Bot::Button($k, ['config', 'level', $v, $k]);
        })->values()->toArray();

        return new InlineKeyboardMarkup(array_chunk($data, 2));
    }

    private function removeChilds()
    {
        return function (Crawler $c) {
            foreach ($c as $node) {
                /* @var DOMElement $node */
                $node->parentNode->removeChild($node);
            }
        };
    }
}
