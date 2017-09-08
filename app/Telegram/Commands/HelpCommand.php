<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Telegram\AbstractCommand;

class HelpCommand extends AbstractCommand
{
    public static $description = '/help Справка по командам лисы';

    public static $entities = ['/help'];
    protected $active       = true;
    protected $visible      = true;
    protected $showPreview  = true;

    protected $patterns = [
        '\/help',
    ];

    public function execute($payload)
    {
        $this->responseText = 'http://telegra.ph/Manual-po-ispolzovaniyu-Lisy-09-08';
    }
}
