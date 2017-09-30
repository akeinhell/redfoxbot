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
    protected $handler;
    private $baseUrl;

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
        $oldRequest = clone $request;
        $authParams = [
            'pinn' => Config::getValue($this->chatId, 'pin')
        ];

        if (!$this->baseUrl) {
            $this->baseUrl = $request->getUri()->getPath();
        }

        $handler = $this->handler;

        $data = http_build_query($authParams, '', '&');
        /** @var Promise $promise */
        $promise = $handler($request
            ->withUri($request->getUri()->withPath('/go/'))
            ->withMethod('POST')
            ->withBody(stream_for($data))
            ->withHeader('Content-Length', strlen($data))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader(self::HEADER_KEY, 'redfoxbot'), $options);

        if (!$this->isAuthenticated($promise->wait())) {
            throw new NotAuthenticatedException($this->chatId);
        };

        $oldUri = $oldRequest->getUri()->withPath($this->baseUrl);

        return $handler($oldRequest->withUri($oldUri), $options)->wait();
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    private function isAuthenticated($response)
    {
        $html = (string)$response->getBody();
        $rus = iconv('cp1251', 'utf8', $html);
        $searchValue = 'name="pinn"';

        return strpos($html, $searchValue) === false || strpos($html, 'index.php') !== false;
    }
}
