<?php

namespace App\Http\Controllers;

use App\Exceptions\TelegramCommandException;
use App\Games\BaseEngine\AbstractGameEngine;
use App\Telegram\AbstractCommand;
use App\Telegram\Bot;
use App\Telegram\CommandParser;
use App\Telegram\Commands\CodeCommand;
use App\Telegram\Commands\SpoilerCommand;
use App\Telegram\Commands\StartCommand;
use App\Telegram\Config;
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

    public function setup()
    {
        $result = [];
        try {
            $result['status'] = Bot::action()->setWebhook(\URL::to('hook'));
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $result['status']  = false;
        }

        return response()->json($result);
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
        header("HTTP/1.1 202");
        ob_flush();
        flush();

        $dataRaw = file_get_contents('php://input');
        $encoded = json_decode($dataRaw, true);
        $update  = Update::fromResponse($encoded);
        $message = $update->getMessage();

        if ($message) {
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
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    private function parseMessage($message)
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
            $this->parseCoords($chatId, $text);
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
     * @param AbstractCommand $command AbstractCommand
     * @param integer $chatId
     * @param integer $from
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
     * @param integer $chatId
     * @param $text
     *
     * @return boolean|null
     */
    private function parseCoords($chatId, $text)
    {
        if ($coords = getCoordinates($text)) {
            list($lon, $lat) = $coords;
            Bot::action()->sendLocation($chatId, $lon, $lat);
        }
    }


    /**
     * @param                     integer $chatId
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
            ->each(function(Crawler $crawler) use (&$links, $domain) {
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
                mb_convert_encoding($string, 'UTF-8', 'UTF-8'),
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
