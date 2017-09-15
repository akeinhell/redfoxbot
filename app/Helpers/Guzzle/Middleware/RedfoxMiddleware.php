<?php


namespace App\Helpers\Guzzle\Middleware;

use App\Helpers\Guzzle\Exceptions\NotAuthenticatedException;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RedfoxMiddleware extends AbstractMiddleware
{
    const HEADER_KEY = 'X_REDFOX_AUTH';
    private $baseUrl;

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

    private function isAuthenticated(ResponseInterface $response): bool
    {
        $location = array_get($response->getHeader('location'), 0, '');
        $html = (string)$response->getBody();
        $searchValue = 'user/login';

        return strpos($location, $searchValue) === false && strpos($html, $searchValue) === false;
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return ResponseInterface
     * @throws NotAuthenticatedException
     */
    public function retry(RequestInterface $request, array $options = []): ResponseInterface
    {
        $oldRequest = $request;
        $authParams = $this->params;

        /** @var Promise $promise */
        $handler = $this->handler;
        if (!$this->baseUrl) {
            $this->baseUrl = $request->getUri()->getPath();
        }
        $data = http_build_query($authParams,'', '&');
        $promise = $handler($request
            ->withUri($request->getUri()->withPath('/user/login'))
            ->withMethod('POST')
            ->withBody(stream_for($data))
            ->withHeader('Content-Length', strlen($data))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader(self::HEADER_KEY, $this->baseUrl), $options);
        if (!$this->isAuthenticated($promise->wait())) {
            throw new NotAuthenticatedException($this->chatId);
        };


        return $handler($oldRequest->withUri($oldRequest->getUri()->withPath($this->baseUrl)), $options)->wait();
    }
}
