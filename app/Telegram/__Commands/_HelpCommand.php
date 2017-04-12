<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Telegram\AbstractCommand;

class __HelpCommand extends AbstractCommand
{
    public static $description = '/help Справка по командам лисы';

    public static $entities = ['/help'];
    protected $active       = true;
    protected $visible      = true;

    protected $patterns = [
        '\/help',
    ];

    public function execute($payload)
    {
        $help = <<<'TEXT'
Лиса приветствует тебя игрок.
Настройки для бота вводить через сайт: http://redfoxbot.ru/

<b>Основные команды:</b>
/help Справка
/spoiler ... - отправка спойлера (сокр. /s)
/quest - Получение текста задания (сокр. /q)
/select - Выбор задания куда бить коды (используется когда выдается много заданий сразу)

Для отправки кода просто напишите его :-) (формат кода англ. буквы и цифры)
Для принудительной отправки кода поставьте перед ним воскл. знак (к пр. !какой-то код)

Лиса автоматически пришлет вам карту если в тексте задания или в сообщении от штаба
будут находится координаты в форматах 55.999999 99.9999999

По всем вопросам вы можете обращаться:
http://vk.com/akeinhell
http://vk.com/foxbot_project
@akeinhell
TEXT;

        $this->responseText = $help;
    }
}
