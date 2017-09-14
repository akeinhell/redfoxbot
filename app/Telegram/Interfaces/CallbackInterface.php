<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 07.09.17
 * Time: 21:10
 */

namespace App\Telegram\Interfaces;


use TelegramBot\Api\Types\CallbackQuery;

interface CallbackInterface
{
    public function run(CallbackQuery $callbackQuery);
}