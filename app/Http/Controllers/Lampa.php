<?php

namespace App\Http\Controllers;

use App\Games\Sender;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Lampa extends Controller
{
    protected $crawler;
    /**
     * @var Client
     */
    protected $client;
    protected $domain;

    public function __construct()
    {
        $this->crawler = new Crawler();
    }

    /**
     * Получение игр на определенном домене
     * @param $domain
     * @return \Illuminate\Http\JsonResponse
     */
    public function games($domain)
    {
        $games = \Lampa::setDomain($domain)->getAnnounceGames();

        return response()->json($games->unique('id')->sortBy('id')->toArray());
    }

    public function commands(Request $request, $domain)
    {
        $this->domain = $domain;
        $this->getClient($domain);
        $gameId = $request->get('gameId');
        if (!$gameId) {
            return response()->json(['error' => 'gameId not set'], 422);
        }
        $cacheKey = sprintf('LampaCrawler:commands:%s:%s', $domain, $gameId);

        if (!($teams = \Cache::get($cacheKey))) {
            $crawler = $this->get('games/' . $gameId . '/enter', true);
            if ($crawler->filter('#login-form')->count()) {
                $crawler = $this->post('/login', [
                    'LoginForm[username]'   => env('LAMPA_LOGIN'),
                    'LoginForm[password]'   => env('LAMPA_PASS'),
                    'LoginForm[rememberMe]' => 1,
                ]);
                if ($crawler->filter('#login-form')->count()) {
                    throw new HttpException('Cannot authorize in lampa');
                }
            }

            $teams = new Collection($crawler->filter('select#GamesTeams_id option')
                ->each(function(Crawler $option) {
                    return [
                        'id'   => $option->attr('value'),
                        'name' => $option->text(),
                    ];
                }));

            \Cache::put($cacheKey, $teams, 10);
        }

        return response()->json($teams->filter(function($team) {
            return array_get($team, 'id');
        }));
    }

    /**
     * @param $domain
     *
     * @return Client
     */
    private function getClient($domain)
    {
        $url        = sprintf('http://%s.lampagame.ru', $domain);
        $cookieFile = storage_path('cookies/lampa_site.jar');
        $jar        = new FileCookieJar($cookieFile, true);
        $stack      = HandlerStack::create();
        $stack->push(
            Middleware::log(
                \Log::getMonolog(),
                new MessageFormatter('[{code}] {method} {uri}')
            )
        );
        $params = [
            'base_uri' => $url,
            'cookies'  => $jar,
            'headers'  => [
                    'User-Agent' => Sender::getUserAgent(),
                ],
            'handler'  => $stack,
        ];
        $this->client = new Client($params);
    }

    /**
     * @param      string $url
     * @param bool $noCache
     *
     * @return Crawler
     */
    private function get($url, $noCache = false)
    {
        $cacheKey = sprintf('LampaCrawler:%s:%s', $this->domain, $url);

        if (!($result = \Cache::get($cacheKey)) || $noCache) {
            $result = $this->client->get($url)->getBody()->__toString();
            \Cache::put($cacheKey, $result, 60);
        }

        return new Crawler($result);
    }

    /**
     * @param string $url
     * @param $array
     *
     * @return Crawler
     */
    private function post($url, $array)
    {
        $result = $this->client->post($url, ['form_params' => $array])->getBody()->__toString();

        return new Crawler($result);
    }
}
