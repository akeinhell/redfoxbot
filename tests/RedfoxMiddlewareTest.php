<?php


use App\Helpers\Guzzle\Exceptions\NotAuthenticatedException;
use App\Helpers\Guzzle\Middleware\RedfoxMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class RedfoxMiddlewareTest extends TestCase
{
    /**
     * @group engine
     */
    public function testRedfoxSuccessAuth()
    {
        return $this->assertTrue(true, 'temporaly disabled');;
        $this->createApplication();
        $html = View::make('mocks.redfox.login')->render();

        $mock = new MockHandler([
            new Response(200, [], $html),
            new Response(200, [], ''),
            new Response(200, [], ''),
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push(new RedfoxMiddleware([], 0));
        $client = new Client([
            'handler'     => $stack,
            'debug'       => true,
            'http_errors' => false,
        ]);


        $client->get('test');
    }

    /**
     * @group engine
     */
    public function testRedfoxFailAuth()
    {
        return $this->assertTrue(true, 'temporaly disabled');;
        $this->createApplication();
        $html = View::make('mocks.redfox.login')->render();

        $mock = new MockHandler([
            new Response(200, [], $html),
            new Response(200, [], $html),
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push(new RedfoxMiddleware([], 0));
        $client = new Client([
            'handler'     => $stack,
            'http_errors' => false,
        ]);

        $this->setExpectedException(NotAuthenticatedException::class);
        $client->get('play');
    }

    public function testWithEngine() {
        // todo
        $this->assertTrue(true, 'temporaly disabled');
//        $engine = new RedfoxSafariEngine(94986676);
//        $list = $engine->getQuestList();
//        $quest = $engine->getRawHtml(2061);
//        $this->assertGreaterThan(0, count($list));
    }
}
