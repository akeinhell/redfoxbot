<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 11.04.16
 * Time: 13:01.
 */

namespace App\Games;

use App\Exceptions\TelegramCommandException;
use App\Helpers\Cookie\S3CookieJar;
use App\Telegram\Config;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;

class Sender
{
    public $lastRequest;
    /**
     * @var Uri
     */
    public $effectiveUrl;
    /**
     * @var Client
     */
    public         $client;
    private static $instance = [];
    private        $jar;
    private        $config;

    /**
     * @var HandlerStack
     */
    public        $stack;

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
        $this->stack = HandlerStack::create();
        $this->stack->push(
            Middleware::log(
                \Log::getMonolog(),
                new MessageFormatter('[{code}] {method} {uri}')
            )
        );
        $params = [
            'base_uri'    => $this->config->url,
            'cookies'     => $this->jar,
            'headers'     => [
                    'User-Agent'       => self::getUserAgent(),
                ],
            'debug'       => env('APP_DEBUG'),
            'handler'     => $this->stack,
            'http_errors' => $this->enableExceptions,
        ];
        $this->client = new Client($params);
    }

    public static function getUserAgent()
    {
        return env('BOT_VERSION') ? 'Telegram Bot v' . env('BOT_VERSION') : 'Telegram Bot';
    }

    /**
     * @param      $chatId
     * @param bool $errors
     *
     * @return Sender
     */
    public static function getInstance($chatId, $errors = true)
    {
        if (!array_key_exists($chatId, self::$instance)) {
            self::$instance[$chatId] = new self($chatId, $errors);
        }

        return self::$instance[$chatId];
    }

    public function sendPost($url, $params = [], $query = [], $requestParams = [])
    {
        $data = array_merge($requestParams, ['form_params' => $params, 'query' => $query]);
        try {
            $response = $this->client->post($url, $data);
        } catch (\Exception $e) {
            throw new TelegramCommandException('Ошибка доступа к движку');
        }
        $this->lastRequest = $response;

        return $this->formatResponse($response);
    }

    public function sendGet($url, $params = [])
    {
        try {
            $response = $this->client->get($url . '?' . http_build_query($params));
        } catch (\Exception $e) {
            \Log::error($e->getMessage(), compact('url', 'params'));

            throw new TelegramCommandException(implode(': ', [
                'Ошибка доступа к движку',
                $e->getMessage()
            ]));
        }

        return $this->formatResponse($response);
    }

    /**
     * @param $chatId
     *
     * @return S3CookieJar
     */
    public static function getCookieFile($chatId)
    {
        $cookieFile = 'cookies/c' . $chatId . '.jar';

        return new S3CookieJar($cookieFile, true);
    }

    private function formatResponse(ResponseInterface $response)
    {
        return (string) $response->getBody();
    }
}
