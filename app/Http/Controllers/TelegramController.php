<?php

namespace App\Http\Controllers;

use App\Events\Telegram\Update as UpdateEvent;
use App\Exceptions\TelegramCommandException;
use App\Games\BaseEngine\AbstractGameEngine;
use App\Telegram\AbstractCommand;
use App\Telegram\Bot;
use App\Telegram\CommandParser;
use App\Telegram\Commands\CodeCommand;
use App\Telegram\Commands\SpoilerCommand;
use App\Telegram\Commands\StartCommand;
use App\Telegram\Config;
use Cache;
use DOMElement;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\DomCrawler\Crawler;
use TelegramBot\Api\HttpException;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\Update;

class TelegramController extends Controller
{
    private $betaChats = [
        94986676, // me
    ];

    public function setup()
    {
        $url = \URL::to('hook');
        dump(Bot::action()->setWebhook($url));
        dump(\Tg::setWebhook([
            'url' => \URL::to('newbot'),
        ]));
    }

    public function generateToken(Request $request)
    {
        $data = $request->all();

        $token = sha1(http_build_query($data));

        $expire = 60 * 10;

        Cache::put(StartCommand::CACHE_KEY_START . $token, json_encode($data), $expire);

        return response()->json(['token' => $token]);
    }

    public function newhook()
    {
        $dataRaw = file_get_contents('php://input');
        $encoded = json_decode($dataRaw, true);
        $update  = Update::fromResponse($encoded);
        $message = $update->getMessage();

        if ($message) {
            event(new UpdateEvent($message, $dataRaw));
            try {
                return $this->parseMessage($message, $dataRaw);
            } catch (TelegramCommandException $e) {
                // FIXME rjcnskm
                try {
                    Bot::action()->sendMessage($message->getChat()->getId(), $e->getMessage());
                } catch (\Exception $e) {
                    Log::error(__LINE__ . $this->formatError($e));
                }
            } catch (HttpException $e) {
                Log::error(__LINE__ . $this->formatError($e));
            } catch (\Exception $e) {
                Log::error(__LINE__ . $this->formatError($e));
            }
        } else {
            Log::alert('Cannot fetch data', [$dataRaw]);
        }

        return response('Null message', 200);
    }

    /**
     * Check emoji from string.
     *
     * @see https://gist.github.com/hnq90/316f08047a3bf348b823
     *
     * @param mixed $str
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

    public function handle(Message $message, $raw)
    {
        $this->saveMessage($message, $raw);

        $chatId = $message->getChat()->getId();
        $config = Config::get($chatId);

        $reply    = false;
        $response = null;
        if ($data = CommandParser::getCommand($message->getEntities(), $message->getText())) {
            list($commandClass, $payload) = $data;
            $response                     = $this->executeCommand($commandClass, $payload, $chatId);
        }

        if (! $data && $config && $action = $this->getAction($message->getText())) {
            list($method, $payload) = $action;
            $reply                  = $message->getMessageId();
            $project                = Config::getValue($chatId, 'project');
            $projectClass           = '\\App\\Games\\Engines\\' . $project . 'Engine';
            try {
                $engine   = new $projectClass($chatId);
                $method   = 'get' . ucfirst($method);
                $response = call_user_func_array([$engine, $method], [$payload]);
            } catch (\Exception $e) {
                Log::alert($e->getMessage());
            }
        }

        if ($response) {
            $additionalAnswers = $this->getExtraResponse($response);
            $response          = $this->parseResponse($response);

            foreach ($response as $text) {
                Bot::action()->sendMessage($chatId, $text, $reply);
            }
        }

        if ($text = $message->getText()) {
            $this->parseCoords($chatId, $text);
        }
    }

    public function newbot()
    {
        $update = \Tg::getWebhookUpdates();
    }

    private function formatError(\Exception $e)
    {
        return sprintf('%s [%s:%s]', $e->getMessage(), $e->getFile(), $e->getLine()) . PHP_EOL . $e->getTraceAsString();
    }

    /**
     * @param Message $message
     * @param mixed   $raw
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    private function parseMessage($message, $raw)
    {
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();
        if ($data = CommandParser::getCommand($message->getEntities() ?: [], $message->getText())) {
            list($className, $payload) = $data;
            /** @var AbstractCommand $class */
            $class = new $className($chatId, $userId);
            $class->execute($payload);
            $this->exec($class, $chatId, $message->getMessageId());

            return response('', 201);
        }

        if ($text = $message->getText()) {
            if ($this->parseCoords($chatId, $text)) {
            }
        }

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

        try {
            $this->tryParseEmoji($message);
        } catch (\Exception $e) {
            Log::info($e->getMessage(), [$e->getTraceAsString()]);
        }
    }

    /**
     * @param $command AbstractCommand
     * @param $chatId
     * @param $from
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
    private function tryParseEmoji($message)
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
     * @param $chatId
     * @param $text
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

        if ($coords = $this->getCoords2($text)) {
            list($lon, $lat) = $coords;
            Bot::action()->sendLocation($chatId, $lon, $lat);

            return true;
        }

        return false;
    }

    /**
     * @param string $commandClass
     * @param string $payload
     * @param int    $chatId
     *
     * @return AbstractCommand
     */
    private function executeCommand($commandClass, $payload, $chatId)
    {
        /** @var AbstractCommand $command */
        $command = new $commandClass($chatId);
        $command->execute($payload);

        return $command->getResponseText() ? null : [$command->getResponseText(), $command->getResponseReply()];
    }

    /**
     * @param string $text
     *
     * @return bool
     */
    private function getAction($text)
    {
        if ($method = $this->checkEmoji($text)) {
            return [$method, null];
        }

        $hotKeys = [
            '!'  => 'sendCode',
            '\?' => 'sendSpoiler',
        ];
        foreach ($hotKeys as $hotKey => $method) {
            $pattern = sprintf('/^%s(.*?)$/i', $hotKey);
            if (preg_match($pattern, $text, $match)) {
                return [$method, $match[1]];
            }
        }

        return null;
    }

    /**
     * @param string $response
     *
     * @return array
     */
    private function getExtraResponse($response)
    {
        $return = [];
        if ($coords = $this->getCoords($response)) {
            $return['sendLocation'] = $coords;
        }

        return $return;
    }

    private function parseResponse($response)
    {
        $response = html_entity_decode(strip_tags($response, '<b><strong><i><code><a><pre>'));
        $parts    = explode(PHP_EOL, chunk_split($response, 1800, PHP_EOL));

        return array_filter($parts, function ($part) {
            return $part;
        });
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

        return null;
    }

    private function getCoords2($text)
    {
        $pattern = '/([\d]{1,3})°\s*([\d]{1,2})\'\s*([\d\.]+)"/isu';
        if (preg_match_all($pattern, $text, $match)) {
            $lon = $this->convertCoords($match[1][0], $match[2][0], $match[3][0]);
            $lat = $this->convertCoords($match[1][1], $match[2][1], $match[3][1]);

            return [$lon, $lat];
        }

        $pattern = '/([0-9]+)\s([0-9]+)\s([0-9.]+)/isu';
        if (preg_match_all($pattern, $text, $match)) {
            $lon = $this->convertCoords($match[1][0], $match[2][0], $match[3][0]);
            $lat = $this->convertCoords($match[1][1], $match[2][1], $match[3][1]);

            return [$lon, $lat];
        }
    }

    private function convertCoords($deg, $min, $sec)
    {
        return round($deg + ((($min * 60) + ($sec)) / 3600), 8);
    }

    /**
     * @param                     $chatId
     * @param                     $message
     * @param ReplyKeyboardMarkup $keyboard
     * @param int|null            $replyTo
     */
    private function sendMessage($chatId, $message, $keyboard = null, $replyTo = null)
    {
        $cr     = new Crawler($message);
        $domain = Config::getValue($chatId, 'url', '');
        $links  = [];
        $cr->filter('img')
            ->each(function (Crawler $crawler) use (&$links, $domain) {
                $link = sprintf('%s', $crawler->attr('src'));
                if (! strpos('http', $link)) {
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
                if (preg_match('/' . $tagPattern . '/isu', $string) && ! preg_match('/<\/' . $tag . '>/isu', $string)) {
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
