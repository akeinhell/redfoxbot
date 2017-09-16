<?php


namespace App\Helpers\Guzzle\Middleware;

use App\Helpers\Guzzle\Exceptions\NotAuthenticatedException;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class EncodingMiddleware
{
    private $handler;

    /**
     * @param callable $handler
     *
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        $this->handler = $handler;

        return function (RequestInterface $request, array $options) use ($handler) {
            /** @var Promise $promise */
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) use ($request, $options) {
                $html = (string) $response->getBody();

                $html = preg_replace('/<meta.*?1251">/', '', $html);
                return $response->withBody(stream_for(iconv('cp1251', 'utf8', $html)));
            });
        };
    }
}
