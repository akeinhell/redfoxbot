<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 10.04.16
 * Time: 15:35.
 */

namespace App\Exceptions;

class TelegramCommandException extends \Exception
{
    private $chatid;

    public function __construct($message, $chatId)
    {
        $this->message = $message;
        $this->chatid = $chatId;
    }

    /**
     * @return int
     */
    public function getChatid(): int
    {
        return $this->chatid;
    }
}
