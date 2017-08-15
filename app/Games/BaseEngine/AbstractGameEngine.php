<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 11.04.16
 * Time: 12:45.
 */

namespace App\Games\BaseEngine;

use App\Games\Sender;
use App\Telegram\Config;

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

    abstract public function sendCode($code);

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
  
    protected function checkConfig()
    {
        $this->config = Config::get($this->chatId);
    }

    protected function getCacheKey()
    {
        if (!Config::getValue($this->chatId, 'activeQuest')) {
            return null;
        }

        return self::GAME_ID . $this->chatId . ':' . Config::getValue($this->chatId, 'activeQuest');
    }
}
