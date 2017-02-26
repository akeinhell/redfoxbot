<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 28.04.16
 * Time: 17:06.
 */

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use TorControl\TorControl;

class Tor
{
    private static $instance;
    private $tor;

    /**
     * Tor constructor.
     */
    public function __construct()
    {
        $this->tor = new TorControl(
            [
                'hostname'   => '127.0.0.1',
                'port'       => 9051,
                'password'   => '',
                'authmethod' => 1,
            ]
        );
        $this->tor->connect();
        $this->tor->authenticate();

        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                \Log::getMonolog(),
                new MessageFormatter('[{code}] {method} {uri}')
            )
        );
        $params = [
            'headers' => [
                    'User-Agent' => getenv('BOT_NAME') ?: 'TelegramBot',
                ],
            'proxy'   => 'socks5://localhost:9050',
            'debug'   => getenv('APP_DEBUG'),
            'handler' => $stack,
        ];
        $this->client = new Client($params);
    }

    public static function action()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function changeIp()
    {
        try {
            $this->tor->executeCommand('SIGNAL NEWNYM');

            return (string) $this->client->get('https://api.ipify.org/')->getBody();
        } catch (\Exception $e) {
            \Log::critical('Cannot change IP:' . $e->getMessage());

            return false;
        }
    }
}
