<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

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
        $gameId = $request->get('gameId');
        if (!$gameId) {
            return response()->json(['error' => 'gameId not set'], 422);
        }

        $commands = \Lampa::setDomain($domain)
            ->setGame($gameId)
            ->getGameCommands();

        return response()->json($commands);
    }
}
