<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Telegram\AbstractCommand;
use Redis;

class TrackingCommand extends AbstractCommand
{
    public static $description = 'tracking';

    public static $entities = ['/tracking'];
    protected $active       = true;
    protected $visible      = true;
    protected $patterns     = [
        '\/tracking',
    ];

    public function __construct($chatId, $fromId = null, $text = null)
    {
        parent::__construct($chatId, $fromId, $text);
    }

    public function execute($payload)
    {
        $this->responseText = \Track::addChat($this->chatId) ?
            'Добавлено отслеживание для этого чата':
            'Ошибка добавления отслеживания';
    }
}
