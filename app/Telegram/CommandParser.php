<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 22.04.16
 * Time: 10:26.
 */

namespace App\Telegram;

use App\Telegram\Commands\ConfigCommand;
use App\Telegram\Commands\HelpCommand;
use App\Telegram\Commands\KeyboardCommand;
use App\Telegram\Commands\QuestCommand;
use App\Telegram\Commands\SelectQuestCommand;
use App\Telegram\Commands\StartCommand;
use App\Telegram\Commands\TrackingCommand;
use TelegramBot\Api\Types\MessageEntity;

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

class CommandParser
{
    public static $commands = [
        StartCommand::class,
        TrackingCommand::class,
//        CodeCommand::class,
//        SpoilerCommand::class,
        QuestCommand::class,
        HelpCommand::class,
        SelectQuestCommand::class,
//        SectorCommand::class,
        ConfigCommand::class,
        KeyboardCommand::class,
    ];

    public static function parse($text, $chatId)
    {
        $return = new \stdClass();

        /** @var AbstractCommand $class */
        foreach (self::$commands as $class) {
            $class = '\\' . $class;
            /** @var AbstractCommand $command */
            $command = new $class($chatId);

            $text = preg_replace('#@redfoxbot#isu', '', $text);
            if (null !== ($payload = $command->checkPattern($text))) {
                $return->command = $class;
                $return->payload = $payload;

                return $return;
            }
        }
    }

    /**
     * @param MessageEntity[] $entities
     * @param string|null     $text
     *
     * @return array|null
     */
    public static function getCommand(array $entities, $text)
    {
        $commands = [];
        /** @var AbstractCommand $class */
        foreach (self::$commands as $class) {
            foreach ($class::$entities as $entity) {
                $commands[$entity] = $class;
            }
        }
        if ($entities && is_array($entities)) {
            foreach ($entities as $entity) {
                /* @var MessageEntity */
                if ($entity->getType() === 'bot_command') {
                    $command = substr($text, $entity->getOffset(), $entity->getLength());
                    $payload = trim(substr($text, strlen($command)));
                    $command = preg_replace('/@\w+$/i', '', $command);
                    $class   = array_get($commands, $command);
                    if ($class) {
                        return [$class, $payload];
                    }
                }
            }
        }

        return null;
    }
}
