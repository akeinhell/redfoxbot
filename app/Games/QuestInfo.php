<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 26.06.2016
 * Time: 0:35
 */

namespace App\Games;


class QuestInfo
{
    private $questText;

    /**
     * @return mixed
     */
    public function getQuestText()
    {
        return $this->questText;
    }

    /**
     * @param mixed $questText
     * @return $this
     */
    public function setQuestText($questText)
    {
        $this->questText = $questText;
        return $this;
    }

}