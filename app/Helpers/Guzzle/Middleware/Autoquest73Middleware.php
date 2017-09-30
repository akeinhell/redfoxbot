<?php


namespace App\Helpers\Guzzle\Middleware;

use App\Games\Interfaces\PinEngine;
use App\Helpers\Guzzle\Exceptions\NotAuthenticatedException;
use App\Telegram\Config;
use GuzzleHttp\Promise\Promise;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Autoquest73Middleware extends AbstractMiddleware implements PinEngine
{
    private $handler;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool
     * @throws NotAuthenticatedException
     */
    public function needAuthorisation(RequestInterface $request, ResponseInterface $response): bool
    {
        if (!$request->hasHeader(self::HEADER_KEY)) {
            return !$this->isAuthenticated($response);
        }

        if (!$this->isAuthenticated($response)) {
            throw new NotAuthenticatedException($this->chatId);
        }

        return false;
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return ResponseInterface
     * @throws NotAuthenticatedException
     * @throws \Throwable
     */
    public function retry(RequestInterface $request, array $options = []): ResponseInterface
    {
        $authParams = [
            'pinn' => Config::getValue($this->chatId, 'pin')
        ];

        $handler = $this->handler;

        $data = http_build_query($authParams,'', '&');
        /** @var Promise $promise */
        $promise = $handler($request
            ->withUri($request->getUri()->withPath('/go/'))
            ->withMethod('POST')
            ->withBody(stream_for($data))
            ->withHeader('Content-Length', strlen($data))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader(self::HEADER_KEY, 'redfoxbot'), $options);

        $response = $promise->wait();
        if (!$this->isAuthenticated($response)) {
            throw new NotAuthenticatedException($this->chatId);
        };


        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    private function isAuthenticated($response)
    {
        $html = (string)$response->getBody();
        $searchValue = 'name="pinn"';

        return strpos($html, $searchValue) === false;
    }
}
