<?php

namespace App\Services\Tracking;

use App\Telegram\Bot;
use Redis;

/**
 * Класс для работы с tracking
 * Class TrackingService
 * @package App\Services\Tracking
 */
class TrackingService
{
    const TRACKING_PREFIX = 'TRACKING:';
    const TRACKING_CHATS  = 'CHAT_LIST';

    public function addChat($id)
    {
        $status = Redis::sadd(self::TRACKING_PREFIX . self::TRACKING_CHATS, [$id]);
        $msg    = $status ? 'Добавлено отслеживание для этого чата' :
            'Вы уже включили отслеживание для этого чата :-) Не стоит тыкать много раз, от этого ничего не изменится';

        Bot::action()->sendMessage($id, $msg);

        return $status;
    }

    public function getChatList()
    {
        return Redis::smembers(self::TRACKING_PREFIX . self::TRACKING_CHATS);
    }

    public function removeChat($id, $reason = '')
    {
        $status = Redis::srem(self::TRACKING_PREFIX . self::TRACKING_CHATS, $id);
        if ($status) {
            Bot::action()->sendMessage($id, 'Отслеживание заданий для данного чата отключено' . PHP_EOL . $reason);
        }

        return $status;
    }
}
