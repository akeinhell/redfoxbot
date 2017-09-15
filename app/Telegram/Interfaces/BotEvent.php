<?php


namespace App\Telegram\Interfaces;


interface BotEvent
{
    public static function validator(): \Closure;
    public static function handle();
}