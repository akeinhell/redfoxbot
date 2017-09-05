<?php


namespace App\Telegram\Handlers;


use TelegramBot\Api\Types\Update;

abstract class BaseHandler
{
    /**
     * @var Update
     */
    protected $update;

    /**
     * @return \Closure
     */
    public function __invoke()
    {
        $handler = &$this;
        return function (...$args) use (&$handler) {
            return call_user_func_array([$handler, 'run'], $args);
        };
    }


    public function getHandler(): \Closure {
        return $this->__invoke();
    }
}