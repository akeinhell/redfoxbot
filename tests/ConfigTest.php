<?php


use App\Telegram\Config;

class ConfigTest extends TestCase
{
    public function testSender()
    {
        $chatId = $this->getRandomChatId();

        $this->assertNull(\Config::get(str_random()));
        
        $randomKey = str_random();
        $randomVal = str_random();

        Config::setValue($chatId, $randomKey, $randomVal);

        $this->assertEquals($randomVal, Config::getValue($chatId, $randomKey));
    }

}