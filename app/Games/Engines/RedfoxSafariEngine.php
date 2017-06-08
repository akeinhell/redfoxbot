<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 11.04.16
 * Time: 12:56.
 */

namespace App\Games\Engines;

use App\Exceptions\NoQuestSelectedException;
use App\Exceptions\TelegramCommandException;
use App\Games\BaseEngine\RedfoxBaseEngine;
use App\Games\Interfaces\CanTrackingInterface;
use App\Telegram\Config;

class RedfoxSafariEngine extends RedfoxBaseEngine implements CanTrackingInterface
{
    public function getQuestList()
    {
        $html = $this->getSender()->sendGet('/play/safari');

        if (!$this->checkAuth($html)) {
            $this->doAuth();
            $html = $this->getSender()->sendGet('/play/safari');
        }

        $pattern = '#a href="\/play\/safari\/([0-9]+)">(.*?)<#isu';
        if (preg_match_all($pattern, $html, $matches)) {
            $questList = [];
            for ($i = 0; $i < count($matches[0]); ++$i) {
                $questId             = $matches[1][$i];
                $questText           = $matches[2][$i];
                $questList[$questId] = $questText;
            }

            return $questList;
        }

        echo $html;

        throw new TelegramCommandException('Не найдено списка заданий', __LINE__);
    }

    public function getBaseParams()
    {
        $this->checkQuestSelected();
        $taskId = Config::getValue($this->chatId, self::QUEST_ID);
        if (!$taskId) {
            throw new NoQuestSelectedException();
        }

        return ['task_id' => $taskId];
    }

    protected function getUrl($type)
    {
        switch ($type) {
            case self::CODE_URL:
            case self::QUEST_URL:
            case self::SPOILER_URL:
                $this->checkQuestSelected();
                break;
        }

        $url = null;
        switch ($type) {
            case self::CODE_URL:
                $url = '/play/submit';
                break;
            case self::QUEST_URL:
                $url = '/play/safari/' . Config::getValue($this->chatId, self::QUEST_ID);
                break;
            case self::SPOILER_URL:
                $url = '/play/submitspoiler';
                break;
            case self::QUEST_LIST_URL:
                $url = '/play/safari';
                break;
            default:
                throw new \Exception('Не опознанная ошибка.', __LINE__);
        }

        return $url;
    }

    private function checkQuestSelected()
    {
        if (!Config::getValue($this->chatId, self::QUEST_ID)) {
            throw new NoQuestSelectedException();
        }
    }

    public function getRawHtml($levelId = null)
    {
        $url = '/play/safari/' . $levelId;
        $response = $this->getSender()->sendGet($url);
        if (!$this->checkAuth($response)) {
            $this->doAuth();
            $response = $this->getSender()->sendGet($url);
        }

        return $response;
    }
}
