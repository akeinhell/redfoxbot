<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Telegram\AbstractCommand;

class QuestCommand extends AbstractCommand
{
    public static $description = 'Получение текста текущего задания';

    public static $entities = ['/q', '/quest'];
    protected $active = true;
    protected $visible = true;
    protected $patterns = [
        '\/q',
        '\/quest',
    ];

    public function __construct($chatId, $fromId = null, $text = null)
    {
        parent::__construct($chatId, $fromId, $text);
    }

    public function execute($payload)
    {
        $response = $this->getEngine()->getQuestText();
        $response = is_array($response) ? array_pad($response, 2, null) : [$response, null];
        list($text, $keyboard) = $response;
        $this->responseText = $text;
        $this->responseKeyboard = $keyboard;
    }
}
