<?php


namespace App\Helpers\Guzzle\Middleware;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractMiddleware
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $params;
    protected $handler;

    public function __construct(array $params = [])
    {
        $this->params = $params;
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
            /** @var Promise $promise */
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) use ($request, $options) {
                if ($this->needAuthorisation($request, $response)) {
                    return $this->retry($request, $options);
                }

                return $response;
            });
        };
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool
     */
    abstract public function needAuthorisation(RequestInterface $request, ResponseInterface $response): bool;

    abstract public function retry(RequestInterface $request, array $options = []): ResponseInterface;
}
