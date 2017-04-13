<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 12.04.17
 * Time: 20:11
 */

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class HelpCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'help';

    /**
     * @var string Command Description
     */
    protected $description = 'Получение справки о боте';

    /**
     * {@inheritdoc}
     */
    public function handle($arguments)
    {
        $commands = $this->telegram->getCommands();

        $text = <<<'TEXT'
Лиса приветствует тебя игрок.
Настройки для бота вводить через сайт: http://redfoxbot.ru/

Список основных команд:

TEXT;
        foreach ($commands as $name => $handler) {
            $text .= sprintf('/%s - %s' . PHP_EOL, $name, $handler->getDescription());
        }

        $text .= <<<TEXT

Для отправки кода просто напишите его :-) (формат кода англ. буквы и цифры)
Для принудительной отправки кода поставьте перед ним воскл. знак (к пр. !какой-то код)

Лиса автоматически пришлет вам карту если в тексте задания или в сообщении от штаба
будут находится координаты в форматах 55.999999 99.9999999

По всем вопросам вы можете обращаться:
http://vk.com/akeinhell
http://vk.com/foxbot_project
@akeinhell
TEXT;


        $this->replyWithMessage(compact('text'));
    }
}
