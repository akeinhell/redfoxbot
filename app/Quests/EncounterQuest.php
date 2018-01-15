<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 19.07.16
 * Time: 12:44.
 */

namespace App\Quests;

use Illuminate\Support\Collection;

class EncounterQuest extends BaseQuest
{
    private $data = [];

    /**
     * EncounterQuest constructor.
     *
     * @param string $html
     **/
    public function __construct($html)
    {
        $this->data = json_decode($html, true);
        parent::__construct($html);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return print_r($this, true);
    }

    public function isAuth()
    {
        // todo add preg_match
        return $this->data;
    }

    public function isRunning()
    {
        return $this->data && $this->getLevel() !== null;
    }

    public function getText()
    {
        $tasks = array_get($this->getLevel(), 'Tasks');

        return $tasks ? format_text(array_get(current($tasks), 'TaskText')) : null;
    }

    public function getHints()
    {
        return $this->getHintsCollection()->toArray();
    }

    public function getHintsText()
    {
        return $this
            ->getHintsCollection()
            ->filter(function ($hint) {
                return array_get($hint, 'time', 0) === 0;
            })
            ->map(function ($hint) {
                return '<b>Подсказка №' . array_get($hint, 'number') . '</b>' . PHP_EOL . array_get($hint,
                    'text') . PHP_EOL;
            })
            ->toArray();
    }

    public function getHintsTime()
    {
        return $this
            ->getHintsCollection()
            ->filter(function ($hint) {
                return array_get($hint, 'time', 0) > 0;
            })
            ->map(function ($hint) {
                return '<b>Подсказка №' . array_get($hint, 'number') . '</b> через ' . array_get($hint,
                    'formattedTime') . PHP_EOL;
            })
            ->toArray();
    }

    /**
     * @return Collection
     */
    public function getHintsCollection()
    {
        return collect(array_merge(
            array_get($this->getLevel(), 'Helps', []),
            array_get($this->getLevel(), 'PenaltyHelps', [])
        ))
            ->map(function ($hint) {
                $time = array_get($hint, 'RemainSeconds');

                return [
                    'id'            => array_get($hint, 'HelpId'),
                    'number'        => array_get($hint, 'Number'),
                    'text'          => format_text(array_get($hint, 'HelpText')),
                    'time'          => $time,
                    'formattedTime' => format_time($time),
                    'penalty'       => array_get($hint, 'IsPenalty') === 1,
                ];
            });
    }

    public function getHint($id)
    {
        // TODO: Implement getHint() method.
    }

    public function getImages()
    {
        // TODO: Implement getImages() method.
    }

    public function getCoordinates()
    {
        // TODO: Implement getCoordinates() method.
    }

    public function getTime()
    {
        return format_time(array_get($this->getLevel(), 'TimeoutSecondsRemain', 0));
    }

    public function getSpoiler()
    {
        return null;
    }

    public function getTitle()
    {
        return array_get($this->getLevel(), 'Name');
    }

    public function getId()
    {
        return array_get($this->getLevel(), 'LevelId');
    }

    public function getQuests()
    {
        // TODO: Implement getQuests() method.
    }

    public function getBonuses()
    {
        /*
         * [BonusId] => 758039
         * [Name] => 44:18
         * [Number] => 15
         * [Task] =>
         * [Help] =>
         * [IsAnswered] =>
         * [Expired] =>
         * [SecondsToStart] => 0
         * [SecondsLeft] => 0
         * [AwardTime] => 0
         * [Answer] =>
         */
        return array_get($this->getLevel(), 'Bonuses', []);
    }

    public function getActiveBonuses()
    {
        return $this->getCodes('Bonuses');
    }

    public function getEstimatedCodes()
    {
        return $this->getCodes('Sectors');
    }

    public function getLevelNumber()
    {
        return array_get($this->getLevel(), 'Number');
    }

    public function getCodeStatus($code)
    {
        $bonuses = $this->getEngineActions('BonusAction');
        $levels  = $this->getEngineActions('LevelAction');
        $bonus   = array_get($bonuses, 'Answer');
        $level   = array_get($levels, 'Answer');

        if ($bonus) {
            $status = 'Бонусный код ' . (array_get($bonuses, 'IsCorrectAnswer') ? 'принят' : 'не принят');

            return sprintf('<b>%s</b>', $status);
        }

        if ($level) {
            $status = 'Код ' . (array_get($levels, 'IsCorrectAnswer') ? 'принят' : 'не принят');

            return sprintf('<b>%s</b>', $status);
        }

        return 'Ошибка определения кода';
    }

    public function getGameTitle()
    {
        return array_get($this->data, 'GameTitle');
    }

    /**
     * @return mixed
     */
    private function getLevel()
    {
        $level = array_get($this->data, 'Level', []);

        return $level;
    }

    public function getMappedLevels()
    {
        $level = $this->getLevel();

        /** @var array $bonuses */
        $bonuses = compact(array_get($level, 'Bonuses', []))
            ->map(function ($bonus) {
                return [
                    'id' => array_get($bonus, 'BonusId'),
                    'title' => array_get($bonus, 'Name'),
                ];
            })
            ->keyBy('id')
            ->all()
        ;

        $levels = compact(array_get($level, 'Levels', []))
            ->map(function ($level) {
                return [
                    'id' => array_get($level, 'LevelId'),
                    'title' => array_get($level, 'LevelName'),
                ];
            })
            ->keyBy('id')
            ->all()
        ;

        dump([
            'l' => $levels,
            'b' => $bonuses,
        ]);

        $fn = function ($res, $val) {
            $id = array_get($val, 'id');
            $title = array_get($val, 'id');
            $res[$id] = $title;

            return $res;
        };

        $res =  array_reduce(array_merge($bonuses, $levels), $fn, []);
        dump($res);
        return $res;
    }

    /**
     * @param string $arrayKey
     */
    private function getCodes($arrayKey)
    {
        /** @var Collection $codes */
        $codes = collect(array_get($this->getLevel(), $arrayKey, []));

        return $codes
            ->filter(function ($code) {
                return !array_get($code, 'IsAnswered');
            })
            ->map(function ($code) {
                return array_get($code, 'Name');
            })
            ->groupBy(function ($item, $key) {
                return $item;
            })
            ->map(function ($item, $key) {
                return $key . (count($item) > 1 ? sprintf(' (%s шт)', count($item)) : '');
            })
            ->toArray();
    }

    /**
     * @param string $action
     *
     * @return array
     */
    private function getEngineActions($action = null)
    {
        $actions = array_get($this->data, 'EngineAction', []);

        return $action ? array_get($actions, $action, []) : $actions;
    }
}
