<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Telegram\AbstractCommand;

class SpoilerCommand extends AbstractCommand
{
    public static $description = 'Отправка спойлера в двиг';

    public static $entities = ['/spoiler'];
    protected $active       = true;
    protected $visible      = true;
    protected $patterns     = [
        '\/spoiler ',
        '\?',
    ];

    public function execute($payload)
    {
        $this->responseText  = $this->getEngine()->sendSpoiler($payload);
        $this->responseReply = true;
    }
}
