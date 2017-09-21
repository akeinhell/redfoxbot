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
use App\Games\Interfaces\IncludeSectors;
use App\Games\Interfaces\LoginPassEngine;
use App\Telegram\Config;

class RedfoxSafariEngine extends RedfoxBaseEngine implements CanTrackingInterface, LoginPassEngine, IncludeSectors
{
    public function getQuestList()
    {
        $html = $this->client->get('/play/safari');

        $pattern = '#a href="\/play\/safari\/([0-9]+)">(.*?)<#isu';
        $questList = [];
        if (preg_match_all($pattern, (string)$html->getBody(), $matches)) {
            for ($i = 0; $i < count($matches[0]); ++$i) {
                $questId             = $matches[1][$i];
                $questText           = $matches[2][$i];
                $questList[$questId] = $questText;
            }
        }

        return $questList;
    }

    public function getBaseParams()
    {
        $this->checkQuestSelected();
        $taskId = Config::getValue($this->chatId, self::QUEST_ID);
        if (!$taskId) {
            throw new NoQuestSelectedException($this->chatId);
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
            throw new NoQuestSelectedException($this->chatId);
        }
    }

    public function getRawHtml($levelId = null): string
    {
        $url = '/play/safari/' . $levelId;
        return (string)$this->client->get($url)->getBody();
    }
}
