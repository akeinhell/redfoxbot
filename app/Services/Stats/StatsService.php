<?php

namespace App\Services\Stats;

use Domnikl\Statsd\Client;
use Domnikl\Statsd\Connection\UdpSocket;

/**
 * Класс для работы со статистикой
 * Class StatsService
 * @method increment_
 * @mixin Client
 * @package App\Services\Stats
 */
class StatsService
{
    private $statsd;

    public function __construct()
    {
        $this->statsd = new Client(new UdpSocket('redfoxbot.ru', 8125), 'backend');
    }

    public function __call($name, $arguments)
    {
        call_user_func_array([$this->statsd, $name], $arguments);
    }

    public function increment($tkey, $rate = 1)
    {
        $this->statsd->increment($tkey, $rate);
    }
}
