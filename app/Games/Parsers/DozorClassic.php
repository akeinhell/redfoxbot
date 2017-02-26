<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 26.06.2016
 * Time: 0:36
 */

namespace App\Games\Parsers;


use App\Games\QuestInfo;

class DozorClassic extends AbstractParser
{
    /**
     * @var QuestInfo
     */
    private $quest;

    /**
     * @var string
     */
    private $html;

    /**
     * DozorClassic constructor.
     */
    public function __construct()
    {
        $this->quest = new QuestInfo();
    }


    /**
     * @param $html
     * @return QuestInfo
     */
    public function parseHtml($html)
    {
        $this->html = $html;
        $this->quest->setQuestText($this->getQuestText());
        return $this->quest;
    }

    private function getQuestText()
    {
        if (preg_match_all('#<div class=zad>(.*?)</div>#isu', $this->html, $text)) {
            return implode(PHP_EOL . PHP_EOL, $text[0]);
        }

        return 'Ошибка получения текста уровня';
    }
}