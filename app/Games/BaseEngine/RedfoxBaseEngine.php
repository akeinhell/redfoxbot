<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 26.04.16
 * Time: 16:44.
 */

namespace App\Games\BaseEngine;

use App\Exceptions\TelegramCommandException;
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

    public function sendCode($code)
    {
        $url      = $this->getUrl(self::CODE_URL);
        $params   = array_merge(['code' => $code], $this->getBaseParams());
        $response = $this->getSender()->sendPost($url, $params);
        if (!$this->checkAuth($response)) {
            $this->doAuth();
            $response = $this->getSender()->sendPost($url, $params);
        }

        $return = $this->parseResponse($response, $code);

        return $return;
    }

    /**
     * @param string $html
     */
    public function checkAuth($html = null)
    {
        if (!$html) {
            $html = $this->getSender()->sendGet('play', []);
        }

        return !preg_match('#user\/login#i', $html);
    }

    public function doAuth()
    {
        $this->config = Config::get($this->chatId);
        $params       = [
            'email' => $this->config->login,
            'pass'  => $this->config->password,
        ];
        $response = $this->getSender()->sendPost('/user/login', $params);
        if (!$this->checkAuth($response)) {
            throw new \Exception('Ошибка авторизации');
        }
    }

    /**
     * @param string $html
     */
    public function gameIsRunning($html)
    {
        return preg_match('/team_name/i', $html);
    }

    /**
     * @param string $response
     */
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

    public function sendSpoiler($text)
    {
        $url      = $this->getUrl(self::SPOILER_URL);
        $params   = array_merge(['spoiler_code' => $text], $this->getBaseParams());
        $response = $this->getSender()->sendPost($url, $params);
        if (!$this->checkAuth($response)) {
            $this->doAuth();
            $response = $this->getSender()->sendPost($url, $params);
        }

        $return = $this->parseResponse($response, $text);

        return $return;
    }

    public function getQuestText()
    {
        $url      = $this->getUrl(self::QUEST_URL);
        $response = $this->getSender()->sendGet($url);
        if (!$this->checkAuth($response)) {
            $this->doAuth();
            $response = $this->getSender()->sendGet($url);
        }

        if (preg_match('#task_text">(.*?)<ul class="hints">#isu', $response, $match)) {
            $text = $match[1];
            $text = preg_replace('/\s+/', ' ', $text);
            $text = str_replace('</p>', PHP_EOL, $text);

            return html_entity_decode($text, null, 'UTF-8');
        }
        throw new TelegramCommandException('Не возможно получить текст задания', __LINE__);
    }

    public function getQuestHtml()
    {
        $url      = $this->getUrl(self::QUEST_URL);
        $response = $this->getSender()->sendGet($url);
        if (!$this->checkAuth($response)) {
            $this->doAuth();
            $response = $this->getSender()->sendGet($url);
        }

        return $response;
    }

    /**
     * @param string $html
     *
     * @return Collection[]
     */
    public function getNewKo($html)
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
            $_codes    = $_codes->map(function($i) {
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
     * @param string|null $html
     *
     * @return string
     */
    public function getEstimatedCodes($html = null)
    {
        $codes  = $this->getNewKo($html ?: $this->getQuestHtml());
        $result = '';
        foreach ($codes as $type => $collection) {
            $array = $collection
                ->filter(function($i) {
                    return !array_get($i, 'found');
                });
            $count = $array->count();
            /** @var Collection $array */
            $array = $array
                ->groupBy(function($item) {
                    return array_get($item, 'code');
                })
                ->map(function($item, $key) {
                    return $key . (count($item) > 1 ? sprintf(' (%s шт)', count($item)) : '');
                })
                ->toArray();
            $result .= sprintf("<b>%s:</b> %s шт. \n %s\n", $type, $count, trim(implode(PHP_EOL, $array) . PHP_EOL));
        }

        return $result;
    }

    public function getSectors()
    {
        return $this->getEstimatedCodes();
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
     */
    private function parseResponse($response, $code)
    {
        if (!$this->gameIsRunning($response)) {
            if (preg_match('#h3><p>(.*?)<\/p#i', $response, $m)) {
                return $m[1];
            }

            return 'Игра еще не началась (уже закончилась)';
        }

        $KO       = $this->getKO($response, $code);
        $estCodes = $this->getEstimatedCodes($response);
        $status   = $this->getStatus($response);

        return $status . PHP_EOL . $KO . PHP_EOL . $estCodes;
    }

    /**
     * @param string $response
     */
    private function getStatus($response)
    {
        $msg = 'Ошибка отправки';
        if (preg_match('/<p id="message".*?>(.*?)</i', $response, $message)) {
            $msg = $message[1];
        }

        return sprintf('<b>%s</b>', $msg);
    }
}
