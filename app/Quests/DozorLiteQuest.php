<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 14.10.2016
 * Time: 19:46.
 */

namespace App\Quests;

class DozorLiteQuest extends BaseQuest
{
    public function isAuth()
    {
        return $this->crawler->filter('input[value="test_pin"]')->count() === 0;
    }

    public function isRunning()
    {
        return $this->crawler->filter('input[value="entcod"]')->count();
    }

    public function getText()
    {
        if (preg_match('#levelTextBegin-->(.*?)<!--levelTextEnd#isu', $this->html, $data)) {
            return array_get($data, 1);
        }
    }

    public function getHints()
    {
        // TODO: Implement getHints() method.
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
        // TODO: Implement getTime() method.
    }

    public function getSpoiler()
    {
        // TODO: Implement getSpoiler() method.
    }

    public function getTitle()
    {
        // TODO: Implement getTitle() method.
    }

    public function getId()
    {
        // TODO: Implement getId() method.
    }

    public function getQuests()
    {
        // TODO: Implement getQuests() method.
    }

    public function getBonuses()
    {
        // TODO: Implement getBonuses() method.
    }

    public function getActiveBonuses()
    {
        // TODO: Implement getActiveBonuses() method.
    }

    public function getEstimatedCodes()
    {
        // TODO: Implement getEstimatedCodes() method.
    }
}
