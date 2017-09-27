<?php

namespace App\Http\Controllers;

use App\Games\Engines\EncounterEngine;
use App\Telegram\Bot;
use Cache;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

class EncounterController extends Controller
{
    const CACHE_KEY = 'EN_PARSER:';
    /**
     * @var Client
     */
    private $client;
    private $domain;

    /**
     * EncounterController constructor.
     */
    public function __construct()
    {
    }

    public function parse($domain)
    {
        $this->domain = $domain;
        $this->client = new Client(['base_uri' => sprintf('http://%s.en.cx', $domain === 'demo.en.cx' ? 'demo' : 'moscow')]);
        $games        = [];

        foreach ($this->getParams() as $param) {
            $games = array_merge($games, $this->getGames($param));
        }

        return response()->json(collect($games)->unique('id')->toArray());
    }

    private function getGames($params)
    {
        $lastPage = null;
        $page     = 1;
        $return   = [];
        $domain   = $this->domain;
        while (true) {
            $params['page'] = $page;
            $crawler        = new Crawler($this->get('GameCalendar.aspx', $params));

            $games = $crawler
                ->filter('tr.infoRow')
                ->reduce(function(Crawler $node, $i) use ($domain) {
                    $gameDomain = $node->filter('td')->eq(3)->text();

                    return preg_match('#^' . $domain . '$#', $gameDomain) === 1;
                })
                ->each(function(Crawler $node, $i) use ($params) {
                    $startText = $node->filter('td')->eq(4)->filter('script')->text();
                    $startText = preg_replace('#.*?String\(\'(.*?)\'\).*#', '$1', $startText);
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

            $return = array_merge($return, $games);
            if (!$lastPage) {
                $pages = $crawler->filter('table')->last()->filter('tr')->first()->filter('a');

                if ($pages->count() === 0) {
                    break;
                }
                $lastPage = (int) $pages->last()->text();
            }

            if ($lastPage < $page) {
                break;
            }

            ++$page;
        }

        return $return;
    }

    /**
     * @param string $url
     *
     * @return mixed
     */
    private function get($url, $params)
    {
        ksort($params);

        $cacheKey = self::CACHE_KEY . ($this->domain === 'demo.en.cx' ? 'demo' : 'global') . ':' . implode(':', $params);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        $response = (string) $this->client->get($url, ['query' => $params])->getBody();

        return Cache::remember($cacheKey, 10, function() use ($response) {
            return $response;
        });
    }

    private function getParams()
    {
        $zones = [
            'Real',
            'Points',
            'Virtual',
//            'Quiz',
//            'PhotoHunt',
//            'PhotoExtreme',
//            'Caching',
//            'WetWars',
//            'Competition',
        ];
        $types  = ['single', 'Team'];
        $status = ['Active', 'Coming'];
        $return = [];
        foreach ($zones as $zone) {
            foreach ($types as $type) {
                foreach ($status as $s) {
                    $data     = ['zone' => $zone, 'status' => $s, 'type' => $type];
                    $return[] = $data;
                }
            }
        }

        return $return;
    }

    public function game($chatId) {
        /** @var EncounterEngine $engine */
        $engine = Bot::getEngineFromChatId($chatId);

        return $engine->getRawHtml();
    }

    public function sendCode(Request $request, $chatId) {

        $form = [];
        foreach ($request->all() as $key => $value) {
            $form[str_replace('_', '.', $key)] = $value;
        }

        /** @var EncounterEngine $engine */
        $engine = Bot::getEngineFromChatId($chatId);

        return $engine->sendRawCode($form);
    }

}
