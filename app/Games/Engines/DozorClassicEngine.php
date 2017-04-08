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
use App\Quests\DozorClassicQuest;
use App\Telegram\Config;
use Carbon\Carbon;
use Goutte\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\DomCrawler\Crawler;

class DozorClassicEngine extends AbstractGameEngine
{
    /**
     * @var Client
     */
    private $client;
    private $cookie;
    private $cookieKey;

    /**
     * @param $chatId
     *
     * @throws TelegramCommandException
     */
    public function __construct($chatId)
    {
        parent::__construct($chatId);
        $teamLogin = Config::getValue($chatId, 'teamLogin');
        $teamPass  = Config::getValue($chatId, 'teamPassword');
        if (!$teamLogin || !$teamPass) {
            throw new TelegramCommandException('team login or password not set');
        }

        $this->cookieKey = 'CLASSIC_COOKIE:' . $chatId;
        $stack           = HandlerStack::create();
        $stack->push(
            Middleware::log(
                \Log::getMonolog(),
                new MessageFormatter('[{code}] {method} {uri}')
            )
        );

        $this->cookie = \Cache::get($this->cookieKey, new CookieJar());
        $this->client = new Client([
            'handler' => $stack,
        ], null, $this->cookie);
        $this->client->setAuth($teamLogin, $teamPass);
    }

    public function __destruct()
    {
        if ($this->cookie) {
            \Cache::put($this->cookieKey, $this->cookie, 5);
        }
    }

    /**
     * @param null|Crawler $crawler
     *
     * @return bool
     */
    public function checkAuth(Crawler $crawler = null)
    {
        $crawler = $crawler ?: $this->get();

        return $crawler->filter('input[value=auth]')->count() === 0;
    }

    /**
     * @throws TelegramCommandException
     *
     * @return Crawler
     */
    public function doAuth()
    {
        try {
            $url     = $this->getUrl();
            $crawler = $this->client->request('GET', $url);
        } catch (\Exception $e) {
            throw new TelegramCommandException('Ошибка авторизации. Неверный логин/пасс команды');
        }
        $form    = $crawler->filter('form')->form();
        $crawler = $this->client->submit($form, [
            'login'    => Config::getValue($this->chatId, 'login'),
            'password' => Config::getValue($this->chatId, 'password'),
        ]);

        if (!$this->checkAuth($crawler)) {
            throw new TelegramCommandException('Ошибка авторизации. Неверный логин пасс для бота');
        }

        return $crawler;
    }

    public function sendCode($code)
    {
        $quest = $this->getQuest();

        /** @var Crawler $crawler */
        $crawler = $quest->getCrawler();

        $form = $crawler->filter('form[name=codeform]');

        if ($form->count()) {
            $response = $this->client->submit($form->form(), [
                'cod' => $code,
            ]);
        } else {
            throw new TelegramCommandException('Не возможно отправить код. Возможно игра не началась или уже закончилась');
        }

        $quest = new DozorClassicQuest($response);
        \Cache::put($this->getKey(), $quest, Carbon::now()->addSeconds(10));

        return $quest->getSystemMessage() . PHP_EOL . $quest->getEstimatedCodes();
    }

    public function sendSpoiler($spoiler)
    {
    }

    public function getQuestText()
    {
        $quest = $this->getQuest()->getQuests();

        return $quest->count() ? $quest->first() : 'Не могу получить задание #' . __LINE__;
    }

    public function getQuestList()
    {
    }

    public function getQuest()
    {
        if (!($quest = \Cache::get($this->getKey()))) {
            $crawler = $this->get();
            if (!$this->checkAuth($crawler)) {
                $crawler = $this->doAuth();
            }

            $quest = new DozorClassicQuest($crawler);
            \Cache::put($this->getKey(), $quest, Carbon::now()->addSeconds(10));
        }

        return $quest;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return sprintf('http://classic.dzzzr.ru/%s/go/', Config::getValue($this->chatId, 'domain'));
    }

    /**
     * @return Crawler
     */
    private function get()
    {
        return $this->client->request('GET', $this->getUrl());
    }

    private function getKey()
    {
        return 'DozorClassik:' . $this->chatId;
    }
}
