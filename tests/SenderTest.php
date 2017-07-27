<?php


use App\Telegram\Config;

class SenderTest extends TestCase
{
    public function testSender()
    {
        $config = new stdClass();
        $config->url = 'http://httpbin.org';
        Config::set(0, $config);
        $this->assertEquals($config->url, Config::getValue(0, 'url'));
        $sender = \App\Games\Sender::getInstance(0);
        $this->assertNotNull($sender->sendGet('/cookies/set?name=value'));
    }

}