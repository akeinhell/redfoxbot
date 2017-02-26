<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Telegram\AbstractCommand;

class CodeCommand extends AbstractCommand
{
    public static $description = '/code Отправка кода в движок';

    public static $entities = ['/code'];
    protected $active       = true;
    protected $visible      = true;
    protected $patterns     = [
        '\/code ',
        '!',
    ];

    public function execute($payload)
    {
        $this->responseText  = $this->getEngine()->sendCode($payload);
        $this->responseReply = true;
    }

    public function checkPattern($text)
    {
        if ($this->config && $this->config->format) {
            $pattern = '#^[' . $this->config->format . ']+$#isu';

            if (preg_match($pattern, $text)) {
                return $text;
            }
        }

        return parent::checkPattern($text);
    }
}
