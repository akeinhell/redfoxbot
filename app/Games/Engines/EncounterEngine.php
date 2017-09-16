<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 03.05.2016
 * Time: 5:53.
 */

namespace App\Games\Engines;

use App\Exceptions\TelegramCommandException;
use App\Games\BaseEngine\EncounterAbstractEngine;
use App\Games\Interfaces\LoginPassEngine;
use App\Games\Sender;
use App\Quests\EncounterQuest;
use App\Telegram\Config;

class EncounterEngine extends EncounterAbstractEngine implements LoginPassEngine
{
    protected $sender;

    /**
     * EncounterEngine constructor.
     *
     * @param $chatId
     */
    public function __construct($chatId)
    {
        parent::__construct($chatId);
        $this->sender = Sender::getInstance($chatId);
    }

    public function sendCode($code)
    {
        /** @var EncounterQuest $quest */
        $quest = $this->getQuest();
        $this->checkRunnigGame($quest);
        $lastLevel = $quest->getId();
        $data      = [
            'LevelId'            => $quest->getId(),
            'LevelNumber'        => $quest->getLevelNumber(),
            'LevelAction.Answer' => $code,
        ];

        $response = $this->sender->sendPost($this->getUrl(), $data, ['json' => 1]);

        if (!$response) {
            throw new TelegramCommandException('Ошибка отправки кода #' . __LINE__, $this->chatId);
        }

        $quest = $this->getQuest($response);

        $status = $quest->getCodeStatus($code) ?: 'Ошибка определения статуса отправки кода';

        if ($lastLevel === $quest->getId()) {
            return $status . PHP_EOL . $this->getEstimatedCodes($quest);
        }

        return $status . PHP_EOL . '<b>Внимание! Получено новое задание.</b>' . PHP_EOL . $this->getQuestText($quest);
    }

    public function checkAuth()
    {
        $data = $this->sender->sendGet($this->getUrl(), ['json' => 1]);
        $auth = !preg_match('/Login.aspx/isu', $data);

        return $auth ? $data : false;
    }

    /**
     * http://demo.en.cx/gameengines/encounter/play/25005.
     */
    public function doAuth()
    {
        $params = [
            'Login'        => Config::getValue($this->chatId, 'login'),
            'Password'     => Config::getValue($this->chatId, 'password'),
            'btnLogin'     => 'Вход',
            'EnButton1'    => 'Вход',
            'ddlNetwork'   => '1',
            'socialAssign' => '0',
        ];
        $data = $this->sender->sendPost('/Login.aspx?return=' . urlencode($this->getUrl()), $params);
        if (!$this->checkAuth()) {
            throw new TelegramCommandException('Ошибка авторизации', $this->chatId);
        }

        return $data;
    }

    /**
     * @param string $data
     *
     * @return EncounterQuest
     */
    public function getQuest($data = null)
    {
        if (!$data) {
            $data = $this->checkAuth();
            if (!$data) {
                $this->doAuth();
            }
            $url  = $this->getUrl();
            $data = $this->sender->sendGet($url, ['json' => 1]);
        }
        $quest = new EncounterQuest($data);

        return $quest;
    }

    public function getTime()
    {
        return $this->getQuest()->getTime();
    }

    public function sendSpoiler($spoiler)
    {
    }

    /**
     * @param null|EncounterQuest $quest
     *
     * @throws TelegramCommandException
     *
     * @return string
     */
    public function getQuestText($quest = null)
    {
        $q = $quest ?: $this->getQuest();
        $this->checkRunnigGame($q);

        return implode(PHP_EOL, [
            $q->getText(),
            '',
            'Время: ' . $q->getTime(),
        ]);
    }

    public function getQuestList()
    {
    }

    /**
     * @param EncounterQuest $q
     *
     * @throws TelegramCommandException
     *
     * @return bool
     */
    public function checkRunnigGame(EncounterQuest $q)
    {
        if (!$q->isRunning()) {
            $gameTitle = $q->getGameTitle() ? '<b>' . $q->getGameTitle() . '</b> еще не началась' : 'Не могу получить задание, возможно игра не началась';
            throw new TelegramCommandException($gameTitle, $this->chatId);
        }

        return $q->isRunning();
    }

    /**
     * @param EncounterQuest $quest
     *
     * @return string
     */
    public function getEstimatedCodes($quest = null)
    {
        $quest = $quest ?: $this->getQuest();
        $this->checkRunnigGame($quest);
        $sectors = $quest->getEstimatedCodes();
        $bonus   = $quest->getActiveBonuses();

        if ($sectors || $bonus) {
            $response = [];
            if ($sectors) {
                $response[] = 'Основные сектора:';
                foreach ($sectors as $sector) {
                    $response[] = $sector;
                }
            }

            if ($bonus) {
                $response[] = 'Бонусные сектора:';
                foreach ($bonus as $sector) {
                    $response[] = $sector;
                }
            }

            return implode(PHP_EOL, $response);
        }

        return 'Не могу получить список секторов';
    }

    /**
     * @param null|EncounterQuest $quest
     *
     * @throws TelegramCommandException
     *
     * @return string
     */
    public function getHints($quest = null)
    {
        $quest = $quest ?: $this->getQuest();
        $this->checkRunnigGame($quest);
        $result = implode(PHP_EOL, $quest->getHintsText()) . PHP_EOL . implode(PHP_EOL, $quest->getHintsTime());

        return trim($result) ?: 'Подсказок не обнаружено';
    }

    private function getUrl()
    {
        return '/gameengines/encounter/play/' . Config::getValue($this->chatId, 'gameId');
    }
}
