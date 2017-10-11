<?php

namespace App\Services\Encounter;

use App\Games\Sender;
use Cache;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Класс для работы с лампой
 * Class LampaService
 * @package App\Services\Lampa
 */
class EncounterService
{
    const CACHE_KEY = 'EN_SERVICE:';
    private $crawler;
    private $clients = [];


    public function __construct()
    {
        $this->crawler = new Crawler();
    }

    private function getClient($demoSite)
    {
        $id = (int) $demoSite;
        if (!array_get($this->clients, $id)) {
            $stack = HandlerStack::create();
            $stack->push(
                Middleware::log(
                    \Log::getMonolog(),
                    new MessageFormatter('[{code}] {method} {uri}')
                ), 'logger'
            );
            $uri = $demoSite ? 'demo.en.cx' : 'msk.en.cx';
            $params                        = [
                'base_uri' => 'http://' . $uri,
                'cookies'  => Sender::getCookieFile('encounter_parser'),
                'headers'  => [
                    'User-Agent' => Sender::getUserAgent(),
                ],
                'handler'  => $stack,
            ];
            $this->clients[$id] = new Client($params);
        }

        return array_get($this->clients, $id);
    }

    public function getGames($params, $page = 1, $demoSite = false)
    {
        $params['page'] = $page;
        $crawler        = new Crawler($this->get('GameCalendar.aspx', $params, $demoSite));

        $games = $crawler
            ->filter('tr.infoRow')
            ->each(function (Crawler $node, $i) use ($params) {
                $start  = $node->filter('td')->eq(4)->filter('script');
                $startText = $start->count()?$start->text(): null;
                $startText  = preg_replace('#.*?String\(\'(.*?)\'\).*#', '$1', $startText);
                if (!$start->count()) {
                    \Log::info('start not  found', array_merge($params, [
                        'title' => $node->filter('td')->eq(5)->text()
                    ]));
                }
                $start      = $start?new Carbon($startText):Carbon::now();
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

        $lastPage = $pages->count() > 0 ? (int)$pages->last()->text(): null;

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

        return Cache::remember($cacheKey, 60, function () use ($response) {
            return $response;
        });
    }

    private function permutate(Collection $available, $result = []):array
    {
        if ($available->count() == 0) {
            return $result;
        }
        if ($available->count() == 1) {
            $key = $available->keys()->first();
            $res = [];
            foreach ($available->first() as $first) {
                foreach ($result as $k => $values) {
                    dd(get_defined_vars());
                }
            }
            dd(get_defined_vars());
            return $result;
        }
        if ($available->count() > 1) {
            $key = $available->keys()->first();
            $value = $available->shift();
            $mutations = $this->permutate($available, [
                $key => $value
            ]);
            dd('big then one', $mutations);
        }
        print_r(['printr', $available->toArray(), $result]);
        dd();
        return [];
    }

    public function getPermutations($includeFinished = false)
    {
        $zones = [
            'Real',
            'Points',
            'Virtual',
            'Quiz',
            'PhotoHunt',
            'PhotoExtreme',
            'Caching',
            'WetWars',
            'Competition',
        ];
        $types  = ['single', 'Team'];
        $statuses = ['Active', 'Coming'];
        if (!$includeFinished) {
            //            $statuses[] = 'Finished';
        }

        $return = [];
        foreach ($zones as $zone) {
            foreach ($types as $type) {
                foreach ($statuses as $status) {
                    $return[] = compact('status', 'type', 'zone');
                }
            }
        }

        return $return;
    }
}
