<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 18.04.16
 * Time: 11:31.
 */

namespace App\Telegram\Commands;

use App\Telegram\AbstractCommand;
use App\Telegram\Config;

class ConfigCommand extends AbstractCommand
{
    public static $description = 'Выводит текущий конфиг';

    public static $entities = ['/config'];
    protected     $active   = true;
    protected     $visible  = false;
    protected     $patterns = [
        '\/config',
    ];

    /**
     * @param string $payload
     *
     * @return bool
     */
    public function execute($payload): bool
    {
        $config = Config::get($this->chatId);
        if ($config) {
            $ret = [];
            foreach (get_object_vars($config) as $key => $val) {
                $line  = is_array($val) ?
                    sprintf('%s: %s', $key, var_export($val, true)) :
                    sprintf('%s: %s', $key, $val);
                $ret[] = $line;
            }
            $this->responseText = implode(PHP_EOL, $ret);

            return true;
        }
        $this->responseText = 'Нет настроек для данного чата';

        return false;
    }
}
