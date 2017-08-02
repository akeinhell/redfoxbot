<?php


use App\Telegram\Config;

class SenderTest extends TestCase
{
    public function testSender()
    {
        $chatId = $this->getRandomChatId();
        $config = new stdClass();
        $config->url = 'http://httpbin.org';
        Config::set($chatId, $config);
        $this->assertEquals($config->url, Config::getValue($chatId, 'url'));
        $sender = \App\Games\Sender::getInstance($chatId);
        $this->assertNotNull($sender->sendGet('/cookies/set?name=value'));
    }

}