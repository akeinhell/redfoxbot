<?php

namespace App\Services\Redfoxbot;

use App\Telegram\Bot;
use App\Telegram\Config;
use DOMElement;
use Symfony\Component\DomCrawler\Crawler;
use Telegram\Bot\Objects\Update;

/**
 * Class RedfoxbotService
 * @package App\Services\Redfoxbot
 */
class RedfoxbotService
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
     * @param $str
     *
     * @return null|string
     */
    public function checkEmoji($str): ? string
    {
        $array = static::$emoji;
        foreach ($array as $key => $icon) {
            $pattern = '/' . $icon . '/u';
            if (preg_match($pattern, $str)) {
                return $key;
            }
        }

        return null;
    }


    /**
     * @param Update $update
     **/
    public function parseUpdate(Update $update)
    {
        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $this->parseCoords($update);
        $this->parseEmoji($update);

        $format = Config::getValue($chatId, 'format');
        if (!$format) {
            return;
        }

        $auto = Config::getValue($chatId, 'auto', 'true') === 'true';
        if (preg_match('/^[' . $format . ']+$/i', $message->getText()) && $auto) {
            $this->triggerCommand('code', [$message->getText()], $update);
        }
        if (preg_match('/^!(.*?)$/i', $message->getText(), $codes)) {
            $this->triggerCommand('code', [$codes[1]], $update);
        }

        if (preg_match('/^\?(.*?)$/i', $message->getText(), $codes)) {
            $this->triggerCommand('spoiler', [$codes[1]], $update);
        }
    }

    private function triggerCommand(string $name, array $params, Update $update)
    {
        \Telegram::getCommandBus()->execute($name, $params, $update);
    }

    /**
     * @param Update $update
     */
    private function parseEmoji($update)
    {
        $chatId = $update->getMessage()->getChat()->getId();
        $config = Config::get($chatId);
        if ($config && $text = $update->getMessage()->getText()) {
            if ($key = $this->checkEmoji($text)) {
                $method = Bot::$map[$key];
                $this->triggerCommand($method, [], $update);
            }
        }
    }

    /**
     * @param Update $update
     *
     * @return array|bool|null
     */
    private function parseCoords(Update $update): ? array
    {
        if ($coords = $this->getCoords($update->getMessage()->getText())) {
            \Telegram::getCommandBus()->execute('coords', $coords, $update);

            return $coords;
        }

        return null;
    }

    private function getCoords($text)
    {
        $pattern = '/([\d]{1,3}[\.,][\d]{5,})/';
        if (preg_match_all($pattern, $text, $match)) {
            $coords = $match[1];
            if (count($coords) > 1) {
                return [
                    floatval($coords[0]),
                    floatval($coords[1]),
                ];
            }
        }

        $pattern = '/([\d]{1,3})°\s*([\d]{1,2})\'\s*([\d\.]+)"/isu';
        if (preg_match_all($pattern, $text, $match) && count($match) > 1) {
            $lon = $this->convertCoords((int) $match[1][0], (int) $match[2][0], (int) $match[3][0]);
            $lat = $this->convertCoords((int) $match[1][1], (int) $match[2][1], (int) $match[3][1]);

            return [$lon, $lat];
        }

        $pattern = '/([0-9]+)\s([0-9]+)\s([0-9.]+)/isu';
        if (preg_match_all($pattern, $text, $match) && count($match) > 1) {
            $lon = $this->convertCoords((int) $match[1][0], (int) $match[2][0], (int) $match[3][0]);
            $lat = $this->convertCoords((int) $match[1][1], (int) $match[2][1], (int) $match[3][1]);

            return [$lon, $lat];
        }

        return null;
    }

    /**
     * @param int $deg
     * @param int $min
     * @param int $sec
     *
     * @return float
     */
    private function convertCoords($deg, $min, $sec): float
    {
        return round($deg + ((($min * 60) + ($sec)) / 3600), 8);
    }

    /**
     * @param                     integer $chatId
     * @param                             $message
     * @param ReplyKeyboardMarkup $keyboard
     * @param int|null $replyTo
     */
    public function sendMessage($chatId, $message, $keyboard = null, $replyTo = null)
    {
        $cr = new Crawler($message);
        $domain = Config::getValue($chatId, 'url', '');
        $links = [];
        $cr->filter('img')
            ->each(function(Crawler $crawler) use (&$links, $domain) {
                $link = sprintf('%s', $crawler->attr('src'));
                if (!strpos('http', $link)) {
                    $link = $domain . preg_replace('/\.\.\//is', '', $link);
                }
                $links[] = $link;
                foreach ($crawler as $node) {
                    /* @var DOMElement $node */
                    $node->parentNode->removeChild($node);
                }
            });

        $tags = ['b', 'strong', 'i', 'code', 'a', 'pre'];
        $response = strip_tags($message, implode(array_map(function($tag) {
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

            Bot::action()->sendMessage(
                $chatId,
                $string,
                'HTML',
                true,
                $replyTo, // reply
                $keyboard
            );
        }

        foreach ($links as $link) {
            Bot::action()->sendMessage(
                $chatId,
                $link,
                'HTML',
                false
            );
        }
    }
}
