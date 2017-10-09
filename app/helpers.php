<?php

use Carbon\Carbon;

const TRACKING_KEY = 'tracking_chats';

function webpack_asset($type, $file): string
{
    $map = [
        'script' => '<script src="%s"></script>',
        'style' => '<link rel="stylesheet" href="%s"/>',
    ];
    try {
        $manifest = Storage::drive('public')->get('manifest.json');
    } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
        return '';
    }
    $files = json_decode($manifest, true);
    $template = array_get($map, $type, $map['script']);

    $realName = array_get($files, $file);
    if ($realName) {
        return sprintf($template, ('/dist/' . $realName));
    }
    return '';
}

if (!function_exists('format_time')) {
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

if (!function_exists('publish_to_sns')) {
    /**
     * @param $message
     */
    function publish_to_sns($message): void
    {
        try {
            $sns    = new AmazonSNS(env('S3_KEY'), env('S3_SECRET'));
            $topics = $sns->listTopics();
            $sns->publish(current($topics)['TopicArn'], json_encode($message));
        } catch (\Exception $e) {
            Log::critical('Cannot publish to sns: ' . $e->getMessage());
        }
    }
}

if (!function_exists('format_text')) {
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

/**
 * @return Closure
 */
function parseSmallCoords(): Closure
{
    return function (...$args): array {
        return array_map(function ($arg) {
            return (float) $arg;
        }, $args[0]);
    };
}


/**
 * @param $normalized
 *
 * @return array|null
 */
function parseNormalizedCoords($normalized)
{
    $coords = collect($normalized)
        ->filter(function ($coord) {
            return count($coord) > 2;
        });

    if ($coords->count() < 2) {
        return null;
    }

    return array_map(function ($coord) {
        $deg = array_get($coord, 0, 0);
        $min = array_get($coord, 1, 0);
        $sec = array_get($coord, 2, 0) + array_get($coord, 3, 0) / 100;

        return round($deg + ((($min * 60) + ($sec)) / 3600), 8);
    }, $normalized);
}

/**
 * @return Closure
 */
function parseLongCoords(): Closure
{
    return function (...$args): array {
        $normalized = [];
        foreach ($args as $key => $val) {
            foreach ($val as $k => $v) {
                $normalized[$k][$key] = $v;
            }
        }

        return parseNormalizedCoords($normalized);
    };
}

function parseSimpleCoords()
{
    return function (...$args) {
        $matches    = array_pop($args);
        $normalized = array_chunk($matches, floor(count($matches) / 2));

        return parseNormalizedCoords($normalized);
    };
}

function getCoordinates($text)
{
    // @todo delete
    $text = is_array($text)?array_get($text, 0):$text;
    $patterns = [
        '/([\d]{1,3}[\.,][\d]{5,})/isu'                           => parseSmallCoords(),
        '/([\d]{1,3})°\s*([\d]{1,2})\'\s*([\d]+)\.?([\d]+)?"/isu' => parseLongCoords(),
//        '/(?=(\d+)\s?)\1/isu'                                     => parseSimpleCoords(),
    ];

    /**
     * @var  $pattern
     * @var  $callback
     */
    foreach ($patterns as $pattern => $callback) {
        preg_match_all($pattern, $text, $matches);
        if ($matches && count($matches[0]) >= 2) {
            array_shift($matches);

            return $callback->__invoke(...$matches);
        }
    }

    return null;
}
