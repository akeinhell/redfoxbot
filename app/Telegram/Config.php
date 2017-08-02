<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 27.04.16
 * Time: 15:20.
 */

namespace App\Telegram;

use Cache;

class Config
{
    public static $KEY_IP;

    /**
     * @param string $key
     * @param string $default
     */
    public static function getValue($chatId, $key, $default = null)
    {
        $config = self::get($chatId);
        if (!$config || !isset($config->$key)) {
            return $default;
        }

        return $config->$key;
    }

    /**
     * @param       $chatId
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get($chatId, $default = null)
    {
        $config = Cache::get(AbstractCommand::CACHE_KEY_CHAT . $chatId);
        if (!$config) {
            $db = \App\Config::whereChatId($chatId)->first();
            if ($db) {
                $data = json_decode($db->config);
                self::set($chatId, $data);

                return $data;
            }
            return new \stdClass();
        }

        return $config ?: $default;
    }

    /**
     * @param string $key
     */
    public static function setValue($chatId, $key, $value)
    {
        $config = self::get($chatId);

        $config->$key = $value;
        self::set($chatId, $config);
    }

    public static function set($chatId, $data)
    {
        $db         = \App\Config::firstOrNew(['chat_id' => $chatId]);
        $db->config = json_encode($data);
        $db->save();
        $cookie = self::getCookieFile($chatId);
        if (file_exists($cookie)) {
            //            unlink($cookie);
        }
        Cache::put(AbstractCommand::CACHE_KEY_CHAT . $chatId, $data, 60 * 60 * 24);
    }

    public static function getCookieFile($chatId)
    {
        return storage_path('cookies/c' . $chatId . '.jar');
    }
}
