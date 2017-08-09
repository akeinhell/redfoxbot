<?php


use App\Games\Sender;
use App\Helpers\Guzzle\Middleware\BasicAuthMiddleware;
use App\Telegram\Config;
use GuzzleHttp\HandlerStack;

class BasicAuthMiddlewareTest extends TestCase
{
    public function testMiddleware(){
        Config::setValue(0, 'url', 'http://httpbin.org');
        $sender = Sender::getInstance(0, true);
        /** @var HandlerStack $handler */
        $sender->stack->push(new BasicAuthMiddleware($sender->client));
        $response = \GuzzleHttp\json_decode($sender->sendGet('/basic-auth/user/passwd'), true);
        $this->assertEquals(true, array_get($response, 'authenticated'));
    }
}