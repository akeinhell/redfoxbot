<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 11.04.16
 * Time: 13:01
 */

namespace App\Games;

use App\Exceptions\TelegramCommandException;
use App\QuestLog;
use App\Telegram\Config;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;

class Sender
{
    private static $instance = [];
    public $lastRequest;
    /**
     * @var Uri
     */
    public $effectiveUrl;
    /**
     * @var Client
     */
    public $client;
    private $jar;
    private $config;

    public function __construct($chatId, $enableExceptions = true)
    {
        $this->chatId           = $chatId;
        $this->enableExceptions = $enableExceptions;
        $this->updateParams();
    }

    public function updateParams()
    {
        $this->jar    = static::getCookieFile($this->chatId);
        $this->config = Config::get($this->chatId);
        if (!$this->config) {
            \Log::error('Cannot load config', [$this->chatId]);

            return;
        }
        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                \Log::getMonolog(),
                new MessageFormatter('[{code}] {method} {uri}')
            )
        );
        $params       = [
            'base_uri'    => $this->config->url,
            'cookies'     => $this->jar,
            'headers'     =>
                [
                    'User-Agent'       => self::getUserAgent(),
                ],
            'debug'       => getenv('APP_DEBUG') ?: true,
            'handler'     => $stack,
            'http_errors' => $this->enableExceptions,
        ];
        $this->client = new Client($params);
    }

    public static function getUserAgent()
    {
        return getenv('BOT_VERSION') ? 'Telegram Bot v' . getenv('BOT_VERSION') : 'Telegram Bot';
    }

    /**
     * @param      $chatId
     *
     * @param bool $errors
     *
     * @return Sender
     */
    public static function getInstance($chatId, $errors = true)
    {
        if (!array_key_exists($chatId, self::$instance)) {
            self::$instance[$chatId] = new Sender($chatId, $errors);
        }

        return self::$instance[$chatId];
    }

    public function sendPost($url, $params = [], $query = [], $requestParams = [])
    {
        $data = array_merge($requestParams, ['form_params' => $params, 'query' => $query]);
        try {
            $response = $this->client->post($url, $data);
        } catch
        (\Exception $e) {
            throw new TelegramCommandException('Ошибка доступа к движку');
        }
        $this->lastRequest = $response;

        // FIXME костыль :-(
        $safeHTML       = preg_match('/dozor/isu', Config::getValue($this->chatId, 'project', 'unknown')) ?
            iconv('cp1251', 'utf8', $this->formatResponse($response)) : $this->formatResponse($response);
        $logger         = new QuestLog();
        $logger->html   = $safeHTML;
        $logger->url    = Config::getValue($this->chatId, 'url') . $url;
        $logger->engine = Config::getValue($this->chatId, 'project', 'unknown');
        $logger->query  = json_encode($query);
        $logger->form   = json_encode($params);
        $logger->save();

        return $this->formatResponse($response);
    }

    private function formatResponse(ResponseInterface $response)
    {
        return (string)$response->getBody();
    }


    public function sendGet($url, $params = [])
    {
        try {
            $response = $this->client->get($url . '?' . http_build_query($params));
        } catch (\Exception $e) {
            throw new TelegramCommandException('Ошибка доступа к движку');
        }
        $logger = new QuestLog();
        // FIXME костыль :-(
        $safeHTML       = preg_match('/dozor/isu', Config::getValue($this->chatId, 'project', 'unknown')) ?
            iconv('cp1251', 'utf8', $this->formatResponse($response)) : $this->formatResponse($response);
        $logger->html   = $safeHTML;
        $logger->engine = Config::getValue($this->chatId, 'project', 'unknown');
        $logger->url    = Config::getValue($this->chatId, 'url') . $url;
        $logger->query  = json_encode($params);
        $logger->form   = json_encode([]);
        $logger->save();

        return $this->formatResponse($response);
    }

    /**
     * @param $chatId
     * @return FileCookieJar
     */
    public static function getCookieFile($chatId)
    {
        $cookieFile = storage_path('cookies/c' . $chatId . '.jar');

        return new FileCookieJar($cookieFile, true);
    }

}