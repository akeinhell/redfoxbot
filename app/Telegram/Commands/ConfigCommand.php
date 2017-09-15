<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Games\BaseEngine\AbstractGameEngine;
use App\Games\Engines\EncounterEngine;
use App\Games\Interfaces\LoginPassEngine;
use App\Games\Interfaces\PinEngine;
use App\Telegram\AbstractCommand;
use App\Telegram\Bot;
use App\Telegram\Config;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class ConfigCommand extends AbstractCommand
{
    public static $description = 'Выводит текущий конфиг';

    public static $entities = ['/config'];
    protected $active   = true;
    protected $visible  = false;
    protected $patterns = [
        '\/config',
    ];

    /**
     * @param string $payload
     *
     * @return bool
     */
    public function execute($payload): bool
    {
        $textArray = [
            'Вы вошли в режим настройки:',
            '',
            'Текущая конфигурация:',
            Config::toString($this->chatId),
        ];
        $this->responseText = implode(PHP_EOL, $textArray);
        $this->responseKeyboard = $this->getConfigKeyboard($this->chatId);

        return true;
    }

    public static function getConfigKeyboard($chatId)
    {
        $data = [];

        $data[] = [self::getInput($chatId, 'url', 'url')];
        if ($project = Config::getValue($chatId, 'project')) {
            $engine = Config::getValue($chatId, 'project', 'Не указан');
            if ($engine == 'DozorLite') {
                $domain = Config::getValue($chatId, 'domain', 'не указан');
                $data[] = [Bot::Button( 'Город: '. $domain, ['config', 'input', 'url'])];
            }
            $data[] = [Bot::Button( 'Движок: ' . $engine, ['config', 'project'])];

            $projectClass = '\\App\\Games\\Engines\\' . $project . 'Engine';
            /* @var AbstractGameEngine $engine */
            $engine = new $projectClass($chatId);
            if ($engine instanceof LoginPassEngine) {
                $data[] = [self::getInput($chatId, 'login', 'login')];
                $data[] = [self::getInput($chatId, 'password', 'password')];
            }

            if ($engine instanceof PinEngine) {
                $data[] = [self::getInput($chatId, 'pin', 'pin')];
            }

            if (trim($projectClass, '\\') == EncounterEngine::class) {
                $data[] = [self::getInput($chatId, 'gameId', 'id игры')];
            }
        }

        $export = (array) Config::get($chatId);
        $token = sha1(http_build_query($export));
        $expire = 60 * 10;
        \Cache::put(StartCommand::CACHE_KEY_START . $token, json_encode($export), $expire);

        $data[] = [
            Bot::Button( '🔄 сбросить', ['config', 'clean']),
            Bot::Button( '🆗 Завершить', ['config', 'end']),
        ];

        return new InlineKeyboardMarkup($data);
    }

    private static function getInput($chatId, $param, $text)
    {
        $label = $text . ': ' . Config::getValue($chatId, $param, 'Не указан');
        return Bot::Button($label, ['config', 'input', $param]);
    }
}
