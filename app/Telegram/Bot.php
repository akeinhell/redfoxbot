<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 28.04.16
 * Time: 12:56.
 */

namespace App\Telegram;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class Bot
{
    public static $emoji = [
        'time'     => "\xF0\x9F\x95\x90",
        'text'     => "\xF0\x9F\x93\x84",
        'hints'    => "\xF0\x9F\x92\xAC",
        'estCodes' => "\xE2\x98\x91",
    ];

    public static $map = [
        'time'     => 'time',
        'text'     => 'questText',
        'hints'    => 'hints',
        'estCodes' => 'sectors',
    ];

    /**
     * @var int
     */
    public static $me = 94986676;
    private static $instance;

    public static function getKeyboard()
    {
        $keyboard = [
            [self::formatButton('time', 'Время до слива'), self::formatButton('text', 'Текст задания')],
            [self::formatButton('hints', 'Подсказки'), self::formatButton('estCodes', 'Ост. коды')],
        ];

        return new ReplyKeyboardMarkup($keyboard, null, false);
    }

    public static function getSafariKeyboard()
    {
        $keyboard = [
            [
                self::formatButton('text', 'Текст задания'),
                self::formatButton('estCodes', 'Ост. коды'),
            ],
        ];

        return new ReplyKeyboardMarkup($keyboard, null, true);
    }

    public static function action()
    {
        if (self::$instance === null) {
            self::$instance = new BotApi(getenv('TELEGRAM_KEY'));
        }

        return self::$instance;
    }

    private static function formatButton($emoji, $text)
    {
        return sprintf('%s %s', array_get(self::$emoji, $emoji, ''), $text);
    }
}
