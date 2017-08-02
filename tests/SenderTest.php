<?php


use App\Telegram\Config;

class SenderTest extends TestCase
{
    private $cookies = [
        'name' => 'key'
    ];

    private $chatId = 0;
    private $sender;
    private $testUrl;

    public function setUp()
    {
        parent::setUp();
        $this->createApplication();
        $this->testUrl = 'http://httpbin.org';
        Config::setValue($this->chatId, 'url', $this->testUrl);
        $this->sender = \App\Games\Sender::getInstance($this->chatId);
    }

    public function testCookiesSet() {
        $this->assertNotNull($this->sender->sendGet('/cookies/set', $this->cookies));
    }

    public function testSender()
    {
        $this->assertEquals($this->testUrl, Config::getValue($this->chatId, 'url'));

        $response = $this->sender->sendGet('/cookies');
        $json = json_decode($response, true);
        $this->assertEquals(array_get($this->cookies, 'name'), array_get($json, 'cookies.name'));
    }
}