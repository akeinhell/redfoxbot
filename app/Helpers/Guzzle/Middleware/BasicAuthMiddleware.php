<?php


namespace App\Helpers\Guzzle\Middleware;


use Illuminate\Auth\AuthenticationException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class BasicAuthMiddleware extends AbstractMiddleware
{
    const HEADER_KEY = 'X_BASIC_AUTH';

    function needAuthorisation()
    {
        return function(RequestInterface $request, ResponseInterface $response) {
            if (!$request->getHeader(self::HEADER_KEY)) {
                return $response->getStatusCode() === 401;
            }

            if ($response->getStatusCode() === 401) {
                throw new AuthenticationException();
            }

            return false;
        };
    }

    function retry()
    {
        $client = $this->client;

        return function(RequestInterface $request) use ($client) {
            return $client->request($request->getMethod(),
                $request->getUri(), [
                    'auth'    => ['user', 'passwd'],
                    'headers' => $request->withHeader(self::HEADER_KEY, '1')->getHeaders(),
                ]);
        };
    }
}