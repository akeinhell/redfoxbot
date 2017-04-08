<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Games\Engines\EncounterEngine;
use App\Telegram\AbstractCommand;

class SectorCommand extends AbstractCommand
{
    public static $description = 'Получение списка секторов';

    public static $entities = ['/sector', '/est'];
    protected $active       = true;
    protected $visible      = true;
    protected $patterns     = [
        '\/sector',
    ];

    public function execute($payload)
    {
        /** @var EncounterEngine $engine */
        $engine = $this->getEngine();
        if (method_exists($engine, 'getSectors')) {
            $this->responseText = $this->getEngine()->getSectors();
//            $this->responseReply = true;
        }
    }
}
