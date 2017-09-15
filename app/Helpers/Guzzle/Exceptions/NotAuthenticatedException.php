<?php


namespace App\Helpers\Guzzle\Exceptions;


use App\Exceptions\TelegramCommandException;

class NotAuthenticatedException extends TelegramCommandException
{
    public function __construct($chatId = 0, $message = 'Ошибка авторизации')
    {
        parent::__construct($message, $chatId);
    }
}