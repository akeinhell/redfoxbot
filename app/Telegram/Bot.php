<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 28.04.16
 * Time: 12:56.
 */

namespace App\Telegram;

use App\Telegram\Events\CodeEvent;
use App\Telegram\Events\ConfigEvent;
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
        'estCodes' => 'sectors',
    ];

    /**
     * @var int
     */
    public static $me = 94986676;
    private static $instance;
    private static $clientInstance;

    public static function getKeyboard()
    {
        $keyboard = [
            [self::formatButton('time', 'Время до слива'), self::formatButton('text', 'Текст задания')],
            [self::formatButton('hints', 'Подсказки'), self::formatButton('estCodes', 'Ост. коды')],
        ];

        return new ReplyKeyboardMarkup($keyboard, null, true);
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
        self::detectCoords($chatId, $message);
        $cr     = new Crawler($message);
        $domain = Config::getValue($chatId, 'url', '');
        $links  = [];
        $cr->filter('img')
            ->each(function (Crawler $crawler) use (&$links, $domain) {
                $link = sprintf('%s', $crawler->attr('src'));
                if (!strpos('http', $link)) {
                    $link = $domain . preg_replace('/\.\.\//is', '', $link);
                }
                $links[] = utf8_decode($link);
                foreach ($crawler as $node) {
                    /* @var DOMElement $node */
                    $node->parentNode->removeChild($node);
                }
            });

        $tags     = ['b', 'strong', 'i', 'code', 'a', 'pre'];
        $response = strip_tags($message, implode(array_map(function ($tag) {
            return sprintf('<%s>', $tag);
        }, $tags)));
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

    public static function getChatIdfromCallback(CallbackQuery $callbackQuery) {
        return $callbackQuery->getMessage()->getChat()->getId();
    }
}
