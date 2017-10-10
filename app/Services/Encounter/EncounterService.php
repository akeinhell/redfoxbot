<?php

namespace App\Services\Lampa;

use App\Games\Sender;
use Cache;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Класс для работы с лампой
 * Class LampaService
 * @package App\Services\Lampa
 */
class EncounterService
{
    const CACHE_KEY = 'EN_SERVICE:%s:%s';
    private $crawler;
    private $clients = [];


    public function __construct()
    {
        $this->crawler = new Crawler();
    }

    private function getClient($demoSite)
    {
        if (!$this->clients[(int)$demoSite]) {
            $stack = HandlerStack::create();
            $stack->push(
                Middleware::log(
                    \Log::getMonolog(),
                    new MessageFormatter('[{code}] {method} {uri}')
                ), 'logger'
            );
            $params                        = [
                'base_uri' => $demoSite ? 'demo.en.cx' : 'msk.en.cx',
                'cookies'  => Sender::getCookieFile('encounter_parser'),
                'headers'  => [
                    'User-Agent' => Sender::getUserAgent(),
                ],
                'handler'  => $stack,
            ];
            $this->clients[(int)$demoSite] = new Client($params);
        }

        return $this->clients[(int)$demoSite];
    }

    public function getGames($params, $page = 1, $demoSite = false)
    {
        $params['page'] = $page;
        $crawler        = new Crawler($this->get('GameCalendar.aspx', $params, getGames));

        $games = $crawler
            ->filter('tr.infoRow')
            ->each(function (Crawler $node, $i) use ($params) {
                $startText  = $node->filter('td')->eq(4)->filter('script')->text();
                $startText  = preg_replace('#.*?String\(\'(.*?)\'\).*#', '$1', $startText);
                $start      = new Carbon($startText);
                $gameDomain = $node->filter('td')->eq(3)->text();

                return [
                    'id'     => $node->filter('td')->eq(1)->children()->last()->text(),
                    'type'   => $params['zone'],
                    'domain' => $gameDomain,
                    'start'  => $start->toDateTimeString(),
                    'title'  => $node->filter('td')->eq(5)->text(),
                ];
            });

        $pages = $crawler->filter('table')->last()->filter('tr')->first()->filter('a');

        $lastPage = $pages->count() === 0 ? (int)$pages->last()->text(): null;

        return compact('lastPage', 'games');
    }

    /**
     * @param string $url
     *
     * @param        $params
     * @param bool   $demoSite
     *
     * @return mixed
     */
    private function get($url, $params, $demoSite = false)
    {
        ksort($params);

        $cacheKey = self::CACHE_KEY . ($demoSite ? 'demo' : 'global') . ':' . implode(':', $params);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        $response = (string)$this->getClient($demoSite)->get($url, ['query' => $params])->getBody();

        return Cache::remember($cacheKey, 10, function () use ($response) {
            return $response;
        });
    }

}
