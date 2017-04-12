<?php

namespace App\Http\Controllers;

use App\Exceptions\TelegramCommandException;
use App\Games\BaseEngine\AbstractGameEngine;
use App\Telegram\AbstractCommand;
use App\Telegram\Bot;
use App\Telegram\Commands\CodeCommand;
use App\Telegram\Commands\SpoilerCommand;
use App\Telegram\Commands\StartCommand;
use App\Telegram\Config;
use DOMElement;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\DomCrawler\Crawler;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;


class TelegramController extends Controller
{

    public function setup()
    {
        \Telegram::setWebhook([
            'url' => \URL::to('newbot'),
        ]);
    }

    public function generateToken(Request $request)
    {
        $data = $request->all();

        $token = sha1(http_build_query($data));

        $expire = 60 * 10;

        \Cache::put(StartCommand::CACHE_KEY_START . $token, json_encode($data), $expire);

        return response()->json(['token' => $token]);
    }

    public function newhook()
    {
        /** @var Update $update */
        $update = \Telegram::commandsHandler(true);
        $type   = \Telegram::detectMessageType($update);
        if ($type == 'text') {
            try {
                $response = $this->parseMessage($update->getMessage());

                return $response;
            } catch (TelegramCommandException $e) {

                Bot::action()->sendMessage($update->getMessage()->getChat()->getId(), $e->getMessage());
            } catch (\Exception $e) {
                Log::error(__LINE__ . $this->formatError($e));
            }
        }


        return response('Null message', 200);
    }

    /**
     * Check emoji from string.
     *
     * @see https://gist.github.com/hnq90/316f08047a3bf348b823
     *
     * @param string $str
     *
     * @return bool if existed emoji in string
     */
    public function checkEmoji($str)
    {
        $array = Bot::$emoji;
        foreach ($array as $key => $icon) {
            $pattern = '/' . $icon . '/u';
            if (preg_match($pattern, $str)) {
                return $key;
            }
        }

        return false;
    }


    private function formatError(\Exception $e)
    {
        return sprintf('%s [%s:%s]', $e->getMessage(), $e->getFile(), $e->getLine()) . PHP_EOL . $e->getTraceAsString();
    }

    /**
     * @param Message $message
     *
     * @return null
     */
    private function parseMessage($message)
    {
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();

        $this->parseCoords($chatId, $message->getText());
        $this->parseEmoji($message);

        if ($pattern = Config::getValue($chatId, 'format')) {
            $auto = Config::getValue($chatId, 'auto', 'true') === 'true';
            $code = new CodeCommand($chatId, $userId);
            if (preg_match('/^[' . $pattern . ']+$/i', $message->getText()) && $auto) {
                $code->execute($message->getText());
                $this->exec($code, $chatId, $message->getMessageId());
            }
            if (preg_match('/^!(.*?)$/i', $message->getText(), $codes)) {
                $code->execute($codes[1]);
                $this->exec($code, $chatId, $message->getMessageId());
            }

            if (preg_match('/^\?(.*?)$/i', $message->getText(), $codes)) {
                $spoiler = new SpoilerCommand($chatId, $userId);
                $spoiler->execute($codes[1]);
                $this->exec($spoiler, $chatId, $message->getMessageId());
            }
        }
    }

    /**
     * @param AbstractCommand $command AbstractCommand
     * @param integer         $chatId
     * @param integer         $from
     */
    private function exec($command, $chatId, $from)
    {
        if ($text = $command->getResponseText()) {
            $this->parseCoords($chatId, $text);
            $replyTo = $command->getResponseReply() ? $from : null;
            $this->sendMessage($chatId, $text, $command->getResponseKeyboard(), $replyTo);
        }
    }

    /**
     * @param Message $message
     */
    private function parseEmoji($message)
    {
        $chatId = $message->getChat()->getId();
        $config = Config::get($chatId);
        if ($config && $text = $message->getText()) {
            if ($key = $this->checkEmoji($text)) {
                $projectClass = '\\App\\Games\\Engines\\' . $config->project . 'Engine';
                /* @var AbstractGameEngine $engine */
                try {
                    $engine   = new $projectClass($chatId);
                    $method   = 'get' . ucfirst(Bot::$map[$key]);
                    $response = null;
                    if (method_exists($engine, $method)) {
                        $response = $engine->$method();
                    } else {
                        Log::info('method ' . $method . ' not exists in ' . $projectClass);
                    }
                    if ($response) {
                        $this->sendMessage($chatId, $response);
                    }
                } catch (TelegramCommandException $e) {
                    Bot::action()->sendMessage($chatId, $e->getMessage());
                } catch (\Exception $e) {
                    Log::alert($e->getFile() . $e->getMessage(), [$e->getTraceAsString()]);
                }
            }
        }
    }

    /**
     * @param integer $chatId
     * @param         $text
     *
     * @return bool
     */
    private function parseCoords($chatId, $text)
    {
        if ($coords = $this->getCoords($text)) {
            list($lon, $lat) = $coords;
            Bot::action()->sendLocation($chatId, $lon, $lat);

            return true;
        }

        return false;
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
            $lon = $this->convertCoords($match[1][0], $match[2][0], $match[3][0]);
            $lat = $this->convertCoords($match[1][1], $match[2][1], $match[3][1]);

            return [$lon, $lat];
        }

        $pattern = '/([0-9]+)\s([0-9]+)\s([0-9.]+)/isu';
        if (preg_match_all($pattern, $text, $match) && count($match) > 1) {
            $lon = $this->convertCoords($match[1][0], $match[2][0], $match[3][0]);
            $lat = $this->convertCoords($match[1][1], $match[2][1], $match[3][1]);

            return [$lon, $lat];
        }

        return null;
    }

    /**
     * @param string $deg
     * @param string $min
     * @param string $sec
     *
     * @return float
     */
    private function convertCoords($deg, $min, $sec)
    {
        return round($deg + ((($min * 60) + ($sec)) / 3600), 8);
    }

    /**
     * @param                     integer $chatId
     * @param                             $message
     * @param ReplyKeyboardMarkup         $keyboard
     * @param int|null                    $replyTo
     */
    private function sendMessage($chatId, $message, $keyboard = null, $replyTo = null)
    {
        $cr     = new Crawler($message);
        $domain = Config::getValue($chatId, 'url', '');
        $links  = [];
        $cr->filter('img')
            ->each(function (Crawler $crawler) use (&$links, $domain) {
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
