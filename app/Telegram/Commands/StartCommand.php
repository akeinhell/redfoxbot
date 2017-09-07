<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Exceptions\TelegramCommandException;
use App\Games\Sender;
use App\Telegram\AbstractCommand;
use App\Telegram\Bot;
use App\Telegram\Config;
use Cache;
use Log;
use TelegramBot\Api\Types\ReplyKeyboardHide;

class StartCommand extends AbstractCommand
{
    public static $description = 'Запуск лисы';

    public static $entities = ['/start'];
    protected     $active   = true;
    protected     $visible  = false;
    protected     $patterns = [
        '\/start(\s|@redfoxbot\s)',
    ];

    public function execute($payload)
    {
        if (!$payload) {
            $this->responseText  = 'Настройки необходимо вводить на сайте https://redfoxbot.ru';
            $this->responseReply = true;

            return;
        }

        $key  = self::CACHE_KEY_START . $payload;
        $data = Cache::get($key);
        $data = json_decode($data);
        if (!$data) {
            throw new TelegramCommandException('Не корректный формат данных' . PHP_EOL);
        }

        Config::set($this->chatId, $data);
        try {
            $config         = \App\Config::firstOrNew([
                'chat_id' => $this->chatId,
            ]);
            $config->config = json_encode($data);
            $config->save();
        } catch (\Exception $e) {
            Log::error('CAnnot store config' . $e->getMessage());
        }

        $projectClass = '\\App\\Games\\Engines\\' . $data->project . 'Engine';

        if (!class_exists($projectClass)) {
            $this->responseText = 'Лиса не умеет работать с ' . $data->project . PHP_EOL . ' фыр-фыр :-(';

            return;
        }

        switch ($data->project) {
            case 'DozorLite':
            case 'Ekipazh':
                $this->responseText = sprintf('Чат настроен для движка %s (Город: %s)', $data->project, $data->domain);
                break;
            default:
                $msg                = <<<'TAG'
Чат настроен для работы со следующими данными:
Движок: %s
Город: %s
Логин: %s
Пароль: ******
Формат кода: %s
TAG;
                $this->responseText = sprintf($msg, $data->project, $data->url, $data->login, $data->format);
        }

        if ($data->project === 'Encounter') {
            Bot::action()->sendMessage($this->chatId, 'keyboard', null, false, null, Bot::getKeyboard());
        } else {
            $this->responseKeyboard = new ReplyKeyboardHide();
        }
        Sender::getInstance($this->chatId)->updateParams();
    }

    protected function prepare()
    {

    }
}
