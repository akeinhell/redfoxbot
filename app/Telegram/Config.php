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
    const STATE_KEY     = 'CHAT_STATE:';
    const STATE_DEFAULT = 'main';
    public static $KEY_IP;

    /**
     * @param int    $chatId
     * @param string $key
     * @param string $default
     *
     * @return null|string
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
            $return          = new \stdClass();
            $return->chat_id = $chatId;

            return $return;
        }

        return $config ?: $default;
    }

    /**
     * @param        $chatId
     * @param string $key
     * @param        $value
     */
    public static function setValue($chatId, $key, $value)
    {
        $config       = self::get($chatId);
        $config->$key = $value;
        self::set($chatId, $config);
    }

    public static function set($chatId, $data)
    {
        $db         = \App\Config::firstOrNew(['chat_id' => $chatId]);
        $db->config = json_encode($data);
        $db->save();

        Cache::put(AbstractCommand::CACHE_KEY_CHAT . $chatId, $data, 60 * 60 * 24);
    }

    public static function getCookieFile($chatId)
    {
        return storage_path('cookies/c' . $chatId . '.jar');
    }


    public static function setState($chatId, string $state)
    {
        Cache::put(self::STATE_KEY . $chatId, $state, 10);
    }

    public static function getState($chatId): string
    {
        return Cache::get(self::STATE_KEY . $chatId, self::STATE_DEFAULT);
    }

    public static function toString($chatId)
    {
        $msg = 'Нет настроек для данного чата';
        if ($config = self::get($chatId)) {
            $ret = [];foreach (get_object_vars($config) as $key => $val) {
                if (is_array($val)) {
                    continue;
                }
                $line  = sprintf('%s: %s', $key, $val);
                $ret[] = $line;
            }
            if ($ret) {
                $msg = implode(PHP_EOL, array_merge($ret));
            }
        }
        return $msg;
    }
}
