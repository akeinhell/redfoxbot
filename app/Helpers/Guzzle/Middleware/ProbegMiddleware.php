<?php


namespace App\Helpers\Guzzle\Middleware;

use App\Helpers\Guzzle\Exceptions\NotAuthenticatedException;
use App\Telegram\Config;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ProbegMiddleware
{
    private $handler;
    private $pin;

    public function __construct($chatId)
    {
        $this->chatId = $chatId;
    }

    /**
     * @param callable $handler
     *
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        $this->handler = $handler;

        return function (RequestInterface $request, array $options) use ($handler) {
            $query = $request->getUri()->getQuery();
            $params = \GuzzleHttp\Psr7\parse_query($query);
            $params['p'] = Config::getValue($this->chatId, 'pin');
            $params['o'] = Config::getValue($this->chatId, 'level', 0);
            $uri = $request->getUri()->withQuery(http_build_query($params));
            return $handler($request->withUri($uri), $options);
        };
    }
}
