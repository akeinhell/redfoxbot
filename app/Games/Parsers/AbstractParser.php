<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 26.06.2016
 * Time: 0:34
 */

namespace App\Games\Parsers;


use App\Games\QuestInfo;

abstract class AbstractParser
{

    /**
     * @param $html
     * @return QuestInfo
     */
    abstract public function parseHtml($html);
}