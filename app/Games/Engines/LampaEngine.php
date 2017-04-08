<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 08.07.16
 * Time: 14:11.
 */

namespace app\Games\Engines;

use App\Engines\LampaQuest;
use App\Exceptions\TelegramCommandException;
use App\Games\BaseEngine\AbstractGameEngine;
use App\Games\Sender;
use App\Telegram\Config;
use Illuminate\Database\Eloquent\Collection;

class LampaEngine extends AbstractGameEngine
{
    private $jar;

    /**
     * EncounterEngine constructor.
     *
     * @param $chatId
     */
    public function __construct($chatId)
    {
        parent::__construct($chatId);
        $this->sender = Sender::getInstance($chatId, false);
    }

    public function checkAuth()
    {
        $this->sender->sendGet('/game');
    }

    public function doAuth()
    {
        $config = Config::get($this->chatId);
        $login  = Config::getValue($this->chatId, 'login');
        $pass   = Config::getValue($this->chatId, 'password');

        $html = $this->sender->sendPost('login', [
            'LoginForm[username]'   => $login,
            'LoginForm[password]'   => $pass,
            'LoginForm[rememberMe]' => 0,
            'login-button'          => 'Вход',
        ]);

        $quest = new LampaQuest($html);
        if (!$quest->isAuth()) {
            throw  new TelegramCommandException('Ошибка авторизации');
        }

        return $quest;
    }

    public function sendCode($code)
    {
        $cacheKey = sprintf('LAMPA:%d:%d', $this->chatId, $this->userId ?: 0);
        $levels   = $this->getQuest()->getQuests();

        $currentQuest = \Cache::get($cacheKey);

        if (!$currentQuest) {
            list($id, $status) = $this->massSend($levels, $code);
        } else {
            if ($status = $this->doSendCode($code, $currentQuest)) {
                $id = $currentQuest;
            } else {
                list($id, $status) = $this->massSend($levels, $code);
            }
        }

        $text = '';
        if ($id) {
            \Cache::put($cacheKey, $id, 10);
            $level = $this->getQuest()->getQuestById($id);
            $text  = 'Оставшиеся коды' . PHP_EOL . implode(PHP_EOL, array_get($level, 'estCodes'));
        }

        return $this->getCodeStatus($status, $code) . PHP_EOL . $text;
    }

    public function sendSpoiler($spoiler)
    {
        $cacheKey = sprintf('LAMPA:%d:%d', $this->chatId, $this->userId ?: 0);
        $levels   = $this->getQuest()->getQuests();

        $currentQuest = \Cache::get($cacheKey);

        if (!$currentQuest) {
            list($id, $status) = $this->massSendSpoiler($levels, $spoiler);
        } else {
            if ($status = $this->doSendSpoiler($spoiler, $currentQuest)) {
                $id = $currentQuest;
            } else {
                list($id, $status) = $this->massSendSpoiler($levels, $spoiler);
            }
        }

        $text = '';
        if ($id) {
            \Cache::put($cacheKey, $id, 10);
            $level = $this->getQuest()->getQuestById($id);
            $text  = 'Оставшиеся коды' . PHP_EOL . implode(PHP_EOL, array_get($level, 'estCodes'));
        }

        return $this->getCodeStatus($status, $spoiler, 'Спойлер') . PHP_EOL . $text;
    }

    public function getQuestText()
    {
        return $this->getQuest()->getText();
    }

    public function getImages()
    {
        return $this->getQuest()->getImages();
    }

    public function getQuestList()
    {
        return $this->getQuest()->getQuests();
    }

    public function getEstCodes()
    {
    }

    /**
     * @param null|string $html
     *
     * @throws TelegramCommandException
     *
     * @return LampaQuest
     */
    public function getQuest($html = null)
    {
        $html = $html ?: $this->sender->sendGet('/game');

        $quest = new LampaQuest($html);
        if (!$quest->isAuth()) {
            $quest = $this->doAuth();
        }

        if (!$quest->isGameSelected()) {
            $quest = $this->selectGame();
        }

        return $quest;
    }

    /**
     * @param Collection $levels
     * @param string     $code
     *
     * @return array
     */
    private function massSend($levels, $code)
    {
        foreach ($levels->keyBy('id')->keys() as $id) {
            $status = $this->doSendCode($code, $id);
            if ($status) {
                return [$id, $status];
            }
        }

        return [0, 0];
    }

    /**
     * @param Collection $levels
     * @param            $spoiler
     *
     * @return array
     */
    private function massSendSpoiler($levels, $spoiler)
    {
        foreach ($levels->keyBy('id')->keys() as $id) {
            $status = $this->doSendSpoiler($spoiler, $id);

            if ($status) {
                return [$id, $status];
            }
        }

        return [0, 0];
    }

    private function getCodeStatus($data, $code, $label = 'Код')
    {
        switch ($data) {
            case 0:
                $status = 'не принят';
                break;
            case 1:
                $status = 'принят';
                break;
            case 2:
                $status = 'повторно введен';
                break;
            default:
                $status = 'Ошибка определения статуса кода';
        }

        return sprintf('%s "%s" <b>%s</b>', $label, $code, $status);
    }

    /**
     * @param string $action
     */
    private function getUrl($id, $action)
    {
        return implode('/', [null, 'game', $id, $action]);
    }

    private function doSendCode($code, $levelId)
    {
        $response = $this->sender->sendPost($this->getUrl($levelId, 'code'), [
            'GameLog[code]' => $code,
        ], [], ['headers' => ['X-Requested-With' => 'XMLHttpRequest']]);
        $data     = json_decode(trim($response), true);

        return array_get($data, 'isRight');
    }

    private function doSendSpoiler($spoiler, $levelId)
    {
        $response = $this->sender->sendPost($this->getUrl($levelId, 'spoiler'), [
            'GameLog[code]' => $spoiler,
            'ajax'          => 'code-form',
        ], [], ['headers' => ['X-Requested-With' => 'XMLHttpRequest']]);
        $data = json_decode(trim($response), true);

        $data = array_get($data, 'data', []);

        return array_get($data, 'isRight');
    }

    /**
     * @throws TelegramCommandException
     *
     * @return LampaQuest
     */
    private function selectGame()
    {
        $gameId = Config::getValue($this->chatId, 'gameId');
        $teamId = Config::getValue($this->chatId, 'teamId');
        $pass   = Config::getValue($this->chatId, 'teamPass');
        $html   = $this->sender->sendPost('games/' . $gameId . '/enter', [
            'GamesTeams[id]'       => $teamId,
            'GamesTeams[password]' => $pass,
        ]);
        $quest = new LampaQuest($html);

        if (!$quest->isGameSelected()) {
            throw new TelegramCommandException('Ошибка входа в игру');
        }

        return $quest;
    }
}
