<?php 

namespace App\Console\Commands\Parsers;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;


abstract class AbstractParser {

    private static $_instances = array();
    protected $client;

    public static function getInstance() {
        $class = get_called_class();
        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
    }



    public function __construct() {
        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                \Illuminate\Support\Facades\Log::getMonolog(),
                new MessageFormatter('[{code}] {uri}   {method} {host}{target}')
            )
        );
        $this->client = new \GuzzleHttp\Client(
            [
                'base_uri' => 'http://httpbin.org',
                'handler' => $stack,
            ]
        );
    }

    abstract function init();
    abstract function startParse();
    abstract function get($url);
}