<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 28.09.16
 * Time: 10:59.
 */

namespace App\Telegram;

class MsgData
{
    /**
     * @var int
     */
    private $chatId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var bool
     */
    private $reply;

    /**
     * @var int
     */
    private $messageId;

    /**
     * @var string
     */
    private $text;

    /**
     * @return int
     */
    public function getChatId()
    {
        return $this->chatId;
    }

    /**
     * @param int $chatId
     *
     * @return $this
     */
    public function setChatId($chatId)
    {
        $this->chatId = $chatId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReply()
    {
        return $this->reply;
    }

    /**
     * @param bool $reply
     *
     * @return $this
     */
    public function setReply($reply)
    {
        $this->reply = $reply;

        return $this;
    }

    /**
     * @return int
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @param int $messageId
     *
     * @return $this
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    public static function create()
    {
        return new static();
    }
}
