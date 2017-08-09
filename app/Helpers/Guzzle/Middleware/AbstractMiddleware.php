<?php


namespace App\Helpers\Guzzle\Middleware;


use GuzzleHttp\Client;
use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractMiddleware
{
    protected $client;

    function __construct(Client $client)
    {
        $this->client = $client;
    }

    function __invoke(callable $handler)
    {
        $needAuthorisation = $this->needAuthorisation();
        $retry    = $this->retry();

        return function (RequestInterface $request, array $options) use ($handler, $needAuthorisation, $retry) {
            /** @var Promise $promise */
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) use ($request, $needAuthorisation, $retry) {
                if ($needAuthorisation($request, $response)) {
                    return $retry($request);
                }
                return $response;
            });
        };
    }

    abstract function needAuthorisation();

    abstract function retry();
}