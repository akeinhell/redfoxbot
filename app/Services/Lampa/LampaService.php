<?php

namespace App\Services\Lampa;

use App\Games\Sender;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Класс для работы с лампой
 * Class LampaService
 * @package App\Services\Lampa
 */
class LampaService
{
    const CACHE_KEY = 'LAMPA_SERVICE:%s:%s';

    /**
     * @var string
     */
    private $domain;

    /**
     * @var Client
     */
    private $client;

    /** @var  int */
    private $gameId;

    public function __construct()
    {
        $this->crawler = new Crawler();
    }

    /**
     * @param string $url
     * @param bool $force
     * @return Crawler
     * @throws \Exception
     */
    private function fetch(string $url, bool $force = false): Crawler
    {
        if (!$this->client) {
            throw new \Exception('Client is not initialized');
        }
        \Log::debug(__METHOD__, ['domain' => $this->domain, 'url' => $url, 'force' => $force]);
        $cacheKey = sprintf(self::CACHE_KEY, $this->domain, $url);

        $result = \Cache::get($cacheKey);
        if (!$result || $force) {
            \Log::debug('Not cached, fetching from server');
            $result = $this->client->get($url)->getBody()->__toString();
            \Cache::put($cacheKey, $result, 60);
        }

        return new Crawler($result);
    }

    /**
     * @param string $domain
     * @return LampaService
     */
    public function setDomain(string $domain): LampaService
    {
        $this->domain = $domain;
        $this->initClient();

        return $this;
    }

    /**
     * @return Collection
     */
    public function getAnnounceGames(): Collection
    {
        \Log::debug('fetching lampa games');
        if (!$this->domain) {
            \Log::alert(__CLASS__ . ' domain is not set');
            return collect();
        }

        $pageCount = $this->detectPageCount();
        \Log::debug(sprintf('Found %d pages', $pageCount));
        return $this->parseGames($pageCount);
    }

    /**
     * @param int $gameId
     * @return LampaService
     */
    public function setGame(int $gameId): LampaService
    {
        $this->gameId = $gameId;

        return $this;
    }

    /**
     * @return Collection
     * @throws \Exception
     */
    public function getGameCommands(): Collection
    {
        \Log::debug(__METHOD__);
        $url = sprintf('games/%d/enter', $this->gameId);
        $html = $this->fetch($url, true);
        if (!$this->isAuth($html)) {
            $html = $this->doAuth(env('LAMPA_LOGIN'), env('LAMPA_PASS'));
            if (!$this->isAuth($html)) {
                throw  new \Exception('Cannot auth in lampa');
            }
        }

        $teams = collect();
        $teamIterator = $html->filter('#GamesTeams_id option')->getIterator();
        foreach ($teamIterator as $htmlNode) {
            $node = new Crawler($htmlNode);
            $id = $node->attr('value');
            if ($id !== null) {
                $teams->push([
                    'id'   => $id,
                    'name' => $node->text(),
                ]);
            }
        }

        return $teams;
    }

    private function isAuth(Crawler $crawler): bool
    {
        \Log::debug(__METHOD__);
        return $crawler->filter('#login-form')->count() == 0;
    }

    /**
     * @return int
     */
    private function detectPageCount(): int
    {
        \Log::debug('detect pages');
        $firstPage = $this->fetch('/games/announces');
        $paginator = $firstPage->filter('.pagination')->first();
        if (!$paginator->count()) {
            \Log::debug('Pagination not found. May be not yet enought');
            return 1;
        }

        $lastPage = explode('/', $paginator->filter('.last a')->attr('href'));

        return (int)collect($lastPage)->last();
    }

    /**
     * @param int $pageCount
     * @return Collection
     */
    private function parseGames(int $pageCount): Collection
    {
        $pages = collect();
        $games = collect();

        for ($page = 1; $page <= $pageCount; $page++) {
            $url = sprintf('/games/announces/%s', $page);
            $html = $this->fetch($url);
            $pages->push($html);
        }

        $pages->each(function (Crawler $page) use ($games) {
            $gameBlock = $page->filter('#games-list .list-item');
            $gameBlock->each(function (Crawler $game) use ($games) {
                $link = $game->filter('.list-text-name');
                $games->push([
                    'title' => trim($link->text()),
                    'id'    => last(explode('/', $link->attr('href'))),
                ]);
            });
        });

        return $games;
    }


    private function initClient(): void
    {
        $cookieFile = storage_path('cookies/lampa_site.jar');
        $jar = new FileCookieJar($cookieFile, true);
        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                \Log::getMonolog(),
                new MessageFormatter('[{code}] {method} {uri}')
            )
        );
        $params = [
            'base_uri'                      => sprintf('http://%s.lampagame.ru/', $this->domain),
            'cookies'                       => $jar,
            'headers'                       => [
                'User-Agent' => Sender::getUserAgent(),
            ],
            'handler'                       => $stack,
            RequestOptions::ALLOW_REDIRECTS => [
                'max'             => 10,        // allow at most 10 redirects.
                'strict'          => true,      // use "strict" RFC compliant redirects.
                'referer'         => true,      // add a Referer header
                'track_redirects' => true,
            ],
        ];
        \Log::debug(__METHOD__, $params);
        $this->client = new Client($params);
    }

    private function doAuth(string $login, string $pass): Crawler
    {
        \Log::debug(__METHOD__);
        $result = $this->client
            ->post('/login', [
                'form_params' => [
                    'LoginForm[username]' => $login,
                    'LoginForm[password]' => $pass,
                ],
            ]);

        return new Crawler($result->getBody()->__toString());
    }
}
