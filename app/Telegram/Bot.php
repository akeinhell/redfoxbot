<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 28.04.16
 * Time: 12:56.
 */

namespace App\Telegram;

use App\Games\BaseEngine\AbstractGameEngine;
use App\Games\Interfaces\IncludeHints;
use App\Games\Interfaces\IncludeSectors;
use App\Games\Interfaces\IncludeTime;
use App\Telegram\Events\CodeEvent;
use App\Telegram\Events\ConfigEvent;
use App\Telegram\Events\CoordsEvent;
use App\Telegram\Events\EmojiEvent;
use App\Telegram\Handlers\CallbackHandler;
use App\Telegram\Handlers\CommandHandler;
use DOMElement;
use Symfony\Component\DomCrawler\Crawler;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\CallbackQuery;
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
        'estCodes' => 'estimatedCodes',
    ];

    /**
     * @var int
     */
    public static $me = 94986676;
    private static $instance;
    private static $clientInstance;

    public static function getKeyboard($chatId)
    {
        $engine = self::getEngineFromChatId($chatId);
        $keyboard = [
            self::formatButton('text', 'Текст задания'),
            self::formatButton('estCodes', 'Ост. коды'),
        ];

        $map = [
            IncludeHints::class => self::formatButton('hints', 'Подсказки'),
            IncludeTime::class  => self::formatButton('time', 'Время до слива'),
//            IncludeSectors::class  => self::formatButton('time', 'Время до слива'),
        ];

        foreach ($map as $interface => $button) {
            if ($engine instanceof $button) {
                $keyboard[] = $button;
            }
        }
        $keyboard = array_chunk($keyboard, 2);

        return $keyboard ? new ReplyKeyboardMarkup($keyboard, null, true) : null;
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
            self::$instance = new BotApi(env('TELEGRAM_KEY'));
        }

        return self::$instance;
    }

    private static function formatButton($emoji, $text)
    {
        return sprintf('%s %s', array_get(self::$emoji, $emoji, ''), $text);
    }

    /**
     * @return Client
     */
    public static function getClient()
    {
        if (self::$clientInstance === null) {
            $bot = new Client(env('TELEGRAM_KEY'));
            $bot->on(ConfigEvent::handle(), ConfigEvent::validator());
            $bot->on(EmojiEvent::handle(), EmojiEvent::validator());
            $bot->on(CoordsEvent::handle(), CoordsEvent::validator());
            /** @var AbstractCommand $commandClass */
            foreach (CommandParser::$commands as $commandClass) {
                foreach ($commandClass::$entities as $entity) {
                    $bot->command(trim($entity, '/'), (new CommandHandler($commandClass))->getHandler());
                }
            }

            $bot->on(CodeEvent::handle(), CodeEvent::validator());

            $bot->callbackQuery((new CallbackHandler())->__invoke());
            self::$clientInstance = $bot;
        }

        return self::$clientInstance;
    }

    public static function sendMessage($chatId, $message, $keyboard = null, $replyTo = null)
    {
        $message = is_array($message)?array_get($message, 0):$message;
        self::detectCoords($chatId, $message);
        $cr = new Crawler($message);
        $domain = Config::getValue($chatId, 'url', '');
        $links = [];
        $cr->filter('img')
            ->each(function (Crawler $crawler) use (&$links, $domain) {
                $link = sprintf('%s', $crawler->attr('src'));
                $link = preg_replace('/\.\.\//', '', $link);
                if (!strpos($link, 'http') === false) {
                    $link = $domain . preg_replace('/\.\.\//is', '', $link);
                } else {
                    $link = $domain . $link;
                }
                $links[] = utf8_decode($link);
                foreach ($crawler as $node) {
                    /* @var DOMElement $node */
                    $node->parentNode->removeChild($node);
                }
            });

        $message = preg_replace('/<br\s*?\/?>/s', PHP_EOL, $message);
        $tags = ['b', 'strong', 'i', 'code', 'a', 'pre'];
        $response = strip_tags($message, implode(array_map(function ($tag) {
            return sprintf('<%s>', $tag);
        }, $tags)));

        // https://stackoverflow.com/questions/317053
        $magicRegex = '/(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?/isu';
        $response = preg_replace($magicRegex, '$1=\'$2\'', $response);

        foreach (str_split($response, 3600) as $string) {
            foreach ($tags as $tag) {
                $tagPattern = '<' . $tag . '>';
                // @TODO костыль
                if (preg_match('/' . $tagPattern . '/isu', $string) && !preg_match('/<\/' . $tag . '>/isu', $string)) {
                    $string = preg_replace('/' . $tagPattern . '/', '', $string);
                }
            }

            self::action()->sendMessage(
                $chatId,
                mb_convert_encoding($string, 'UTF-8', 'UTF-8'),
                'HTML',
                true,
                $replyTo, // reply
                $keyboard
            );
        }

        foreach ($links as $link) {
            self::action()->sendMessage(
                $chatId,
                $link,
                'HTML',
                false
            );
        }
    }

    public static function detectCoords($chatId, $text)
    {
        if ($coords = getCoordinates($text)) {
            list($lon, $lat) = $coords;
            Bot::action()->sendLocation($chatId, $lon, $lat);
        }
    }

    /**
     * @param              $text
     * @param array|string $data
     *
     * @param array $custom
     * @return array
     */
    public static function Button($text, $data = null, $custom = [])
    {
        $data = is_array($data) ? implode(':', $data) : $data;

        return array_filter(array_merge([
            'text'          => $text,
            'callback_data' => $data,
        ], $custom));
    }

    /**
     * @param CallbackQuery $callbackQuery
     * @return int|string
     */
    public static function getChatIdfromCallback(CallbackQuery $callbackQuery)
    {
        return $callbackQuery->getMessage()->getChat()->getId();
    }

    /**
     * @param $str
     * @return null|string
     */
    public static function getEmoji($str): ?string
    {
        $array = Bot::$emoji;
        foreach ($array as $key => $icon) {
            $pattern = '/' . $icon . '/u';
            if (preg_match($pattern, $str)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param $name
     * @param $chatId
     * @return AbstractGameEngine|null
     */
    public static function getEngineFromString($name, $chatId): ?AbstractGameEngine
    {
        $projectClass = '\\App\\Games\\Engines\\' . $name . 'Engine';

        return class_exists($projectClass) ? new  $projectClass($chatId) : null;
    }

    public static function getEngineFromChatId($chatId)
    {
        return self::getEngineFromString(Config::getValue($chatId, 'project'), $chatId);
    }
}
