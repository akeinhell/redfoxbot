<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 11.04.16
 * Time: 12:45.
 */

namespace App\Games\BaseEngine;

use App\Exceptions\EngineSendSecureException;
use App\Games\Sender;
use App\Telegram\Config;
use App\Tor;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractGameEngine
{
    const QUEST_ID = 'questId';
    const LAST_IP  = 'LAST_IP';
    const GAME_ID  = 'GAME_ID';

    /**
     * @var int
     */
    protected $chatId;
    /**
     * @var \stdClass
     */
    protected $config;
    /**
     * @var Sender
     */
    protected $sender;

    /**
     * @var int
     */
    protected $userId;

    public function __construct($chatId)
    {
        $this->chatId = $chatId;
    }

    abstract public function checkAuth();

    abstract public function doAuth();

    public function sendSecureCode($code)
    {
        if ($this->canSendSecure()) {
            return $this->sendCode($code);
        }
        throw new EngineSendSecureException();
    }

    abstract public function sendCode($code);

    public function sendSecureSpoiler($spoiler)
    {
        if ($this->canSendSecure()) {
            return $this->sendSpoiler($spoiler);
        }
        throw new EngineSendSecureException();
    }

    abstract public function sendSpoiler($spoiler);

    abstract public function getQuestText();

    abstract public function getQuestList();

    public function getSender()
    {
        return new Sender($this->chatId);
    }

    public function setCurrentUser($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param string $response
     * @param string $baseUrl
     *
     * @return string
     */
    public function fixImageUrl($response, $baseUrl)
    {
        $crawler = new Crawler($response);
        $crawler->filter('img')
            ->each(function (Crawler $node) {
                $url = $node->attr('src');

                return $url;
            });
    }

    protected function canSendSecure()
    {
        $url = Config::getValue($this->chatId, 'url');

        if (! $url) {
            \Log::error('No url specified');
            throw new \Exception('No url specified');
        }

        $cacheKey = 'LOGIN:' . $url;
        $login    = Config::getValue($this->chatId, 'login');

        //Если последний раз отправлялось от них значит отправляем еще раз
        if (\Cache::get($cacheKey) === $login) {
            return true;
        }

        if (false === ($ip = $this->changeIp())) {
            return false;
        }

        \Cache::put($cacheKey, $login);

        return true;
    }

    protected function changeIp()
    {
        if (\Cache::has(self::LAST_IP)) {
            return false;
        }

        $ip = Tor::action()->changeIp();

        $expire = Carbon::now()->addSecond(15);
        \Cache::put(self::LAST_IP, $ip, $expire);

        return $ip;
    }

    protected function checkConfig()
    {
        $this->config = Config::get($this->chatId);
    }

    protected function getCacheKey()
    {
        if (! Config::getValue($this->chatId, 'activeQuest')) {
            return null;
        }

        return self::GAME_ID . $this->chatId . ':' . Config::getValue($this->chatId, 'activeQuest');
    }
}
