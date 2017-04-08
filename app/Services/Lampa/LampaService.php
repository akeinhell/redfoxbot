<?php

namespace App\Services\Lampa;


use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class LampaService
{
    const CACHE_KEY = 'LAMPA_SERVICE:%s';

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
     * @param $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        $this->client = new Client(['base_uri' => sprintf('http://%s.lampagame.ru', $domain)]);
        return $this;
    }

    public function getAnnounceGames()
    {
        if (!$this->domain) {
            \Log::alert(__CLASS__ . ' domain is not set');
            return collect();
        }

    }

    /**
     * @param $url
     * @param bool $force
     * @return Crawler
     */
    private function fetch($url, $force = false)
    {
        $cacheKey = sprintf('LampaCrawler:%s:%s', $this->domain, $url);

        $result = \Cache::get($cacheKey);
        if (!$result || $force) {
            $result = $this->client->get($url)->getBody()->__toString();
            \Cache::put($cacheKey, $result, 60);
        }

        return new Crawler($result);
    }
}