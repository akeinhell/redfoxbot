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
use App\Games\Sender;
use Illuminate\Support\Collection;

class DozorLiteEngine extends AbstractGameEngine
{
    /**
     * @param $chatId
     */
    public function __construct($chatId)
    {
        parent::__construct($chatId);
        $this->sender = Sender::getInstance($chatId);
    }

    public function checkAuth()
    {
        $data = $this->getHtml();

        return !preg_match('#test_pin#isu', $data);
    }

    public function doAuth()
    {
        throw new \Exception('not implemented');
    }

    public function sendCode($code)
    {
        $url   = $this->getUrl();
        $html  = $this->getHtml();
        $level = $this->getLevel($html);
        $html  = $this->sender->sendPost($url, [
            'action' => 'entcod',
            'pin'    => $this->config->pin,
            'lev'    => $level,
            'cod'    => iconv('utf8', 'cp1251', $code),
        ]);

        if (preg_match('#<!--errorText-->(.*?)<!--errorTextEnd-->#', $this->iconv($html), $m)) {
            return sprintf('<b>%s</b>', strip_tags($m[1])) . PHP_EOL . $this->getEstimatedCodes($this->iconv($html));
        }

        return 'Статус отправки не известен';
    }

    public function sendSpoiler($spoiler)
    {
        throw new TelegramCommandException('Временно не доступно', __LINE__);
    }

    public function getQuestText($html = null)
    {
        if (!$html) {
            $html = $this->getHtml();
        }
        if (!preg_match_all('#levelTextBegin-->(.*?)<!--levelTextEnd#isu', $html, $matches)) {
            throw new TelegramCommandException('Ошибка получения текста задания', __LINE__);
        }
        $return = $matches[1][0];

        return preg_replace('#</p>#isu', PHP_EOL, $return);
    }

    public function getQuestList()
    {
        throw new TelegramCommandException('Временно не доступно', __LINE__);
    }

    /**
     * @param string $html
     */
    private function getLevel($html)
    {
        return 0;
        if (!preg_match('#<input type=hidden name=lev value=(.*?)>#isu', $html, $m)) {
            throw new TelegramCommandException('Ошибка получения номера уровня', __LINE__);
        }

        return $m[1];
    }

    private function trimUrl($url)
    {
        return trim(trim($url, '/'));
    }

    /**
     * @return string
     */
    private function getUrl()
    {
        $this->checkConfig();
        if (preg_match('/ekipazh/i', $this->config->url)) {
            $url = sprintf('%s/%s/', $this->trimUrl($this->config->url), $this->trimUrl($this->config->domain));
        } else {
            $url = sprintf('http://lite.dzzzr.ru/%s/go/', $this->trimUrl($this->config->domain));
        }

        return $url;
    }

    private function getHtml()
    {
        $url = $this->getUrl();

        return $this->iconv($this->sender->sendGet($url, ['pin' => $this->config->pin]));
    }

    /**
     * @param string $html
     */
    private function iconv($html)
    {
        return iconv('cp1251', 'utf8', $html);
    }

    /**
     * @param string $html
     */
    private function getEstimatedCodes($html)
    {
        if (preg_match('/<!--difficultyCods(.*?)<\/div>/is', $html, $matches)) {
            $x     = explode('<br>', $matches[1]);
            $codes = explode(':', array_get($x, 1, ''), 2);
            /** @var Collection $collect */
            $collect = collect();
            if (count($codes) === 2) {
                $collect = $collect
                    ->merge(explode(',', $codes[1]))
                    ->filter(function($item) {
                        return trim(strip_tags($item)) === trim($item);
                    })
                    ->map(function($item) {
                        return trim(strip_tags($item));
                    });
            }

            $comment = strip_tags(array_get($x, 2, ''));

            return 'Оставшиеся коды: ' . implode(',', $collect->toArray()) . ' ' . $comment;
        }

        return '';
    }
}
