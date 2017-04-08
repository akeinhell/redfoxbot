<?php

namespace App\Services\Lampa;


use GuzzleHttp\Client;
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

    function __construct()
    {
        $this->crawler = new Crawler();
    }

    /**
     * @param $url
     * @param bool $force
     * @return Crawler
     */
    private function fetch(string $url, bool $force = false): Crawler
    {
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
        $this->client = new Client(['base_uri' => sprintf('http://%s.lampagame.ru', $domain)]);
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

        return (int) collect($lastPage)->last();
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

        $pages->each(function(Crawler $page) use ($games) {
            $gameBlock = $page->filter('#games-list .list-item');
            $gameBlock->each(function(Crawler $game) use ($games) {
                $link = $game->filter('.list-text-name');
                $games->push([
                    'title' => trim($link->text()),
                    'id'    => last(explode('/', $link->attr('href'))),
                ]);
            });
        });

        return $games;
    }
}