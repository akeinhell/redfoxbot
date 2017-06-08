<?php

namespace App\Services\Tracking;

use Redis;

/**
 * Класс для работы с tracking
 * Class TrackingService
 * @package App\Services\Tracking
 */
class TrackingService
{
    const TRACKING_PREFIX = 'TRACKING:';
    const TRACKING_CHATS = 'CHAT_LIST';

    public function addChat($id)
    {
        return Redis::sadd(self::TRACKING_PREFIX . self::TRACKING_CHATS, [$id]);
    }

    public function getChatList() {
        return Redis::smembers(self::TRACKING_PREFIX . self::TRACKING_CHATS);
    }

    public function removeChat($id){
        return Redis::srem(self::TRACKING_PREFIX . self::TRACKING_CHATS, $id);
    }
}
