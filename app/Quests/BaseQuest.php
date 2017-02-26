<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 08.07.16
 * Time: 14:14.
 */

namespace App\Quests;

use Symfony\Component\DomCrawler\Crawler;

abstract class BaseQuest
{
    protected $html;
    protected $crawler;

    /**
     * BaseQuest constructor.
     *
     * @param $html
     */
    public function __construct($html)
    {
        $this->html    = $html;
        $this->crawler = new Crawler($html);
    }

    abstract public function isAuth();

    abstract public function isRunning();

    abstract public function getText();

    abstract public function getHints();

    abstract public function getHint($id);

    abstract public function getImages();

    abstract public function getCoordinates();

    abstract public function getTime();

    abstract public function getSpoiler();

    abstract public function getTitle();

    abstract public function getId();

    abstract public function getQuests();

    abstract public function getBonuses();

    abstract public function getActiveBonuses();

    abstract public function getEstimatedCodes();
}
