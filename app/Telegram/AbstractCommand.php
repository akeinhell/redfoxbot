<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:25.
 */

namespace App\Telegram;

use App\Exceptions\TelegramCommandException;
use App\Games\BaseEngine\AbstractGameEngine;

abstract class AbstractCommand
{
    const CACHE_KEY_START = 'START:';
    const CACHE_KEY_CHAT  = 'CHAT_ID:';

    public static $entities = [];

    protected static $description;
    protected $active;
    protected $visible;
    protected $patterns = [];

    /**
     * @var AbstractGameEngine
     */
    protected $engine;
    protected $responseText;
    /**
     * @var
     */
    protected $responseKeyboard;
    protected $responseReply;
    protected $chatId;
    protected $fromId;
    protected $text;

    /**
     * AbstractCommand constructor.
     *
     * @param $chatId
     * @param $fromId
     * @param $text
     */
    public function __construct($chatId, $fromId = null, $text = null)
    {
        $this->chatId = $chatId;
        $this->fromId = $fromId;
        $this->text   = $text;

        $this->config = Config::get($chatId);
        $this->prepare();
        // todo check
//        Bot::action()->sendChatAction($chatId, 'typing');
    }

    /**
     * @return mixed
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @return mixed
     */
    public function getResponseText()
    {
        $text = preg_replace('#</p>#', PHP_EOL, $this->responseText);

        return $text;
    }

    /**
     * @param mixed $responseText
     */
    public function setResponseText($responseText)
    {
        $this->responseText = $responseText;
    }

    /**
     * @return \TelegramBot\Api\Types\ReplyKeyboardMarkup|null
     */
    public function getResponseKeyboard()
    {
        return $this->responseKeyboard;
    }

    /**
     * @param mixed $responseKeyboard
     */
    public function setResponseKeyboard($responseKeyboard)
    {
        $this->responseKeyboard = $responseKeyboard;
    }

    /**
     * @return mixed
     */
    public function getResponseReply()
    {
        return $this->responseReply;
    }

    /**
     * @param mixed $responseReply
     */
    public function setResponseReply($responseReply)
    {
        $this->responseReply = $responseReply;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return static::$description;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $payload
     */
    abstract public function execute($payload);

    public function checkPattern($text)
    {
        foreach ($this->patterns as $pattern) {
            $_pattern = '#^(' . $pattern . ')#isu';
            if (preg_match($_pattern, $text)) {
                return preg_replace($_pattern, '', $text);
            }
        }

        return null;
    }

    public function getEngine()
    {
        $this->engine = null;
        $this->config = Config::get($this->chatId);

        $engineName = Config::getValue($this->chatId, 'project');
        if (!$engineName) {
            throw new TelegramCommandException('Настройки сбились :-(');
        }
        $projectClass = '\\App\\Games\\Engines\\' . $engineName . 'Engine';
        /* @var AbstractGameEngine $engine */
        $this->engine = new $projectClass($this->chatId);
        $this->engine->setCurrentUser($this->fromId);

        return $this->engine;
    }

    protected function prepare()
    {
        if (!$this->config) {
            throw new TelegramCommandException('Cannot get config for chat: ' . $this->chatId);
        }
    }
}
