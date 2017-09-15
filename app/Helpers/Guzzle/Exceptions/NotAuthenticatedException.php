<?php


namespace App\Helpers\Guzzle\Exceptions;


use App\Exceptions\TelegramCommandException;

class NotAuthenticatedException extends TelegramCommandException
{
    public function __construct($message = 'Ошибка авторизации', $chatId)
    {
        parent::__construct($message, $chatId);
    }
}