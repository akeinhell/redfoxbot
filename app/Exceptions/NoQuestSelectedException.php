<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 10.04.16
 * Time: 15:35.
 */

namespace App\Exceptions;

class NoQuestSelectedException extends TelegramCommandException
{
    public function __construct($chatId, $message = 'Не выбрано задание. Выберите с помощью команды /select')
    {
        parent::__construct($message, $chatId);
    }
}
