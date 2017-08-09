<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 26.04.16
 * Time: 16:44.
 */

namespace App\Games\BaseEngine;

use App\Exceptions\TelegramCommandException;
use App\Helpers\Guzzle\Middleware\RedfoxMiddleware;
use App\Telegram\Config;
use Illuminate\Support\Collection;

abstract class RedfoxBaseEngine extends AbstractGameEngine
{
    const BASE_URL       = 1;
    const SPOILER_URL    = 2;
    const CODE_URL       = 3;
    const QUEST_URL      = 4;
    const QUEST_LIST_URL = 5;
    protected $baseUrl;
    protected $sendSpoilerUrl;
    protected $sendCodeUrl;

    public function __construct($chatId)
    {
        parent::__construct($chatId);
        $authParams = [
            'email' => Config::getValue($chatId, 'login'),
            'pass'  => Config::getValue($chatId, 'password'),
        ];
        $this->stack->push(new RedfoxMiddleware($authParams), 'engine:redfox');
    }

    /**
     * @param $code
     *
     * @return string
     */
    public function sendCode($code): string
    {
        $url      = $this->getUrl(self::CODE_URL);
        $response = $this->client->post($url, [
            'form_params' => array_merge(['code' => $code], $this->getBaseParams())
        ]);

        return $this->parseResponse((string)$response->getBody(), $code);
    }

    /**
     * @param integer $type
     */
    abstract protected function getUrl($type);

    protected function getBaseParams()
    {
        return [];
    }

    /**
     * @param string $response
     *
     * @param string $code
     *
     * @return string
     */
    private function parseResponse(string $response, string $code)
    {
        if (!$this->gameIsRunning($response)) {
            if (preg_match('#h3><p>(.*?)<\/p#i', $response, $m)) {
                return array_get($m, 1, '');
            }

            return 'Игра еще не началась (уже закончилась)';
        }

        $KO       = $this->getKO($response, $code);
        $estCodes = $this->getEstimatedCodes($response);
        $status   = $this->getStatus($response);

        return implode(PHP_EOL, [$status, $KO, $estCodes]);
    }

    /**
     * @param string $html
     *
     * @return bool
     */
    public function gameIsRunning($html):bool
    {
        return !!preg_match('/team_name/i', $html);
    }

    public function getKO($response, $code)
    {
        $co = '';
        if (preg_match('/ul class="found_codes">(.*?)<\/ul/isu', $response, $match)) {
            $codeList = explode('</li>', $match[1]);
            foreach ($codeList as $key => $value) {
                $pattern = '/class">(.*?)<.*?code">' . trim($code) . '\s+</isu';
                if (preg_match($pattern, $value, $coMatch)) {
                    $co = sprintf('<b>Код опасности:</b> [%s]', $coMatch[1]);

                    return $co;
                }
            }
        }

        return $co;
    }

    /**
     * @param string|null $html
     *
     * @return string
     */
    public function getEstimatedCodes($html = null):string
    {
        $codes  = $this->getNewKo($html ?: $this->getQuestHtml());
        $result = '';
        foreach ($codes as $type => $collection) {
            $array = $collection
                ->filter(function ($i) {
                    return !array_get($i, 'found');
                });
            $count = $array->count();
            /** @var Collection $array */
            $array  = $array
                ->groupBy(function ($item) {
                    return array_get($item, 'code');
                })
                ->map(function ($item, $key) {
                    return $key . (count($item) > 1 ? sprintf(' (%s шт)', count($item)) : '');
                })
                ->toArray();
            $result .= sprintf("<b>%s:</b> %s шт. \n %s\n", $type, $count, trim(implode(PHP_EOL, $array) . PHP_EOL));
        }

        return $result;
    }

    /**
     * @param string $html
     *
     * @return Collection[]
     */
    public function getNewKo($html):array
    {
        if (!preg_match('/<p class="codes_class">.*?<\/strong>(.*?)<.p>\n/isu', $html, $codesBlock)) {
            return [];
        }
        if (!preg_match_all('/strong>(.*?)<\/strong>(.*?)<\/p>/', $codesBlock[1], $matchedBlocks)) {
            return [];
        }
        $codes = [];
        foreach ($matchedBlocks[1] as $i => $c) {
            $text = trim($matchedBlocks[2][$i], ':');
            /** @var Collection $_codes */
            $_codes    = collect(preg_split('/,/', $text));
            $_codes    = $_codes->map(function ($i) {
                return [
                    'found' => preg_match('/found/', $i),
                    'code'  => strip_tags($i),
                ];
            });
            $codes[$c] = $_codes;
        }

        return $codes;
    }

    /**
     * @return string
     */
    public function getQuestHtml():string
    {
        $url      = $this->getUrl(self::QUEST_URL);
        return (string)$this->client->get($url)->getBody();
    }

    /**
     * @param $response
     *
     * @return string
     */
    private function getStatus($response): string
    {
        $msg = 'Ошибка отправки';
        if (preg_match('/<p id="message".*?>(.*?)</i', $response, $message)) {
            $msg = array_get($message, 1, '');
        }

        return sprintf('<b>%s</b>', $msg);
    }

    /**
     * @param $text
     *
     * @return string
     */
    public function sendSpoiler($text):string
    {
        $url      = $this->getUrl(self::SPOILER_URL);
        $response = $this->client->post($url, [
            'form_params' => array_merge(['spoiler_code' => $text], $this->getBaseParams())
        ]);

        return $this->parseResponse((string)$response->getBody(), $text);
    }

    /**
     * @return string
     * @throws TelegramCommandException
     */
    public function getQuestText()
    {
        $url      = $this->getUrl(self::QUEST_URL);
        $response = $this->client->get($url);

        if (preg_match('#task_text">(.*?)<ul class="hints">#isu', $response, $match)) {
            $text = array_get($match, 1, '');
            $text = preg_replace('/\s+/', ' ', $text);
            $text = str_replace('</p>', PHP_EOL, $text);

            return html_entity_decode($text, null, 'UTF-8');
        }
        throw new TelegramCommandException('Не возможно получить текст задания', __LINE__);
    }

    /**
     * @return string
     */
    public function getSectors()
    {
        return $this->getEstimatedCodes();
    }
}
