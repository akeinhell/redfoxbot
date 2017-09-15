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

class DozorLiteEngine extends AbstractGameEngine implements PinEngine, IncludeHints
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

    private function getInformation($html)
    {
        if (preg_match('#<!--errorText-->(.*?)<!--errorTextEnd-->#', $html, $m)) {
            $text = str_replace('<!--', '', $m[1]);
            $text = str_replace('-->', '', $text);

            return strip_tags($text);
        }
    }

    public function sendCode($code)
    {
        $url   = $this->getUrl();
        $level = $this->getLevel();
        $html  = $this->sender->sendPost($url, [
            'action' => 'entcod',
            'pin'    => Config::getValue($this->chatId, 'pin'),
            'lev'    => $level,
            'cod'    => iconv('utf8', 'cp1251', $code),
        ]);

        if ($info = $this->getInformation($this->iconv($html))) {
            return sprintf('<b>%s</b>', $info) . PHP_EOL . $this->getEstimatedCodes($this->iconv($html));
        }

        return 'Статус отправки не известен';
    }

    public function sendSpoiler($spoiler)
    {
        throw new TelegramCommandException('Временно не доступно', $this->chatId);
    }

    public function getQuestText($html = null)
    {
        if (!$html) {
            $html = $this->getHtml();
        }
        if (!preg_match_all('#levelTextBegin-->(.*?)<!--levelTextEnd#isu', $html, $matches)) {
            if ($info = $this->getInformation($html)) {
                return $info;
            }
            throw new TelegramCommandException('Ошибка получения текста задания', $this->chatId);
        }
        $return = $matches[1][0];

        return preg_replace('#</p>#isu', '<br>', $return);
    }

    public function getQuestList()
    {
        throw new TelegramCommandException('Временно не доступно', $this->chatId);
    }

    /**
     * @return int
     * @throws TelegramCommandException
     * @internal param string $html
     */
    private function getLevel()
    {
        return 0;
        if (!preg_match('#<input type=hidden name=lev value=(.*?)>#isu', $html, $m)) {
            throw new TelegramCommandException('Ошибка получения номера уровня', $this->chatId);
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
        $url = Config::getValue($this->chatId, 'url');
        $domain = Config::getValue($this->chatId, 'domain');

        return sprintf('%s/%s/go/', $this->trimUrl($url), $this->trimUrl($domain));
    }

    private function getHtml()
    {
        $url = $this->getUrl();

        return $this->iconv($this->sender->sendGet($url, ['pin' => Config::getValue($this->chatId, 'pin')]));
    }

    /**
     * @param string $html
     *
     * @return string
     */
    private function iconv($html)
    {
        return iconv('cp1251', 'utf8', $html);
    }

    /**
     * @param string $html
     *
     * @return string
     */
    public function getEstimatedCodes($html = null)
    {
        $html = $html ?? $this->getHtml();
        if (preg_match('/<!--difficultyCods(.*?)<\/div>/is', $html, $matches)) {
            $x     = explode('<br>', $matches[1]);
            $codes = explode(':', array_get($x, 1, ''), 2);
            /** @var Collection $collect */
            $collect = collect();
            if (count($codes) === 2) {
                $collect = $collect
                    ->merge(explode(',', $codes[1]))
                    ->filter(function ($item) {
                        return trim(strip_tags($item)) === trim($item);
                    })
                    ->map(function ($item) {
                        return trim(strip_tags($item));
                    });
            }

            $comment = strip_tags(array_get($x, 2, ''));

            return 'Оставшиеся коды: ' . implode(',', $collect->toArray()) . ' ' . $comment;
        }

        return '';
    }
}
