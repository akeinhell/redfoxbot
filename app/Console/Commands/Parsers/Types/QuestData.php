<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 12.05.16
 * Time: 10:53
 */

namespace App\Console\Commands\Parsers\Types;


use Carbon\Carbon;

class QuestData
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $link;

    /**
     * @var string
     */
    private $placement;

    /**
     * @var string
     */
    private $description;

    /**
     * @var Carbon
     */
    private $start;

    /**
     * @var Carbon
     */
    private $stop;

    /**
     * @var integer
     */
    private $game_id;
    private $event_id;
    private $html_link;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string      $title
     * @param null|string $city
     * @return $this
     */
    public function setTitle($title, $city = null)
    {
        $this->title = $city ? sprintf('[%s] %s', $city, $title) : $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param $project
     * @param $city
     * @param $id
     * @return $this
     */
    public function setKey($project, $city, $id)
    {
        $this->key = sprintf('%s:%s:%s', $project, $city, $id);

        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlacement()
    {
        return $this->placement;
    }

    /**
     * @param string $placement
     * @return $this
     */
    public function setPlacement($placement)
    {
        $this->placement = $placement;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param Carbon $start
     * @return $this
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getStop()
    {
        return $this->stop;
    }

    /**
     * @param Carbon $stop
     * @return $this
     */
    public function setStop($stop)
    {
        $this->stop = $stop;

        return $this;
    }

    /**
     * @return int
     */
    public function getGameId()
    {
        return $this->game_id;
    }

    /**
     * @param int $game_id
     * @return $this
     */
    public function setGameId($game_id)
    {
        $this->game_id = $game_id;

        return $this;
    }

    public function __invoke()
    {
        return get_object_vars($this);
    }

    public function setHtmlLink($link)
    {
        $this->html_link = $link;

        return $this;
    }

    public function setEventId($id)
    {
        $this->event_id = $id;

        return $this;
    }


}