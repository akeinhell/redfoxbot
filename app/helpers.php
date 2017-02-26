<?php

use Carbon\Carbon;

if (! function_exists('format_time')) {
    /**
     * @param $time
     *
     * @return string
     */
    function format_time($time)
    {
        Carbon::setLocale('ru');

        return $time ? Carbon::now()->addSeconds($time)->diff(Carbon::now())->format('%H:%I:%S') : null;
    }
}

if (! function_exists('format_text')) {
    /**
     * Убирает HTML тэги для бота.
     *
     * @param $text
     *
     * @return string
     */
    function format_text($text)
    {
        return $text;
    }
}
