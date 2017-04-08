<?php

namespace App\Console\Commands\Parsers;

use App\Console\Commands\Parsers\Types\QuestData;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DzzzrParser extends AbstractParser
{

    private $urls = [
        'NSK'  => 'http://lite.dzzzr.ru/novosib/',
        'BEL'  => 'http://lite.dzzzr.ru/belovo/',
        'ACH'  => 'http://lite.dzzzr.ru/achinsk/',
        'NVKZ' => 'http://lite.dzzzr.ru/nvkz/',
        'KRSK' => 'http://lite.dzzzr.ru/krsk/',
    ];

    private $placements = [
        'NSK'  => 'Новосибирск',
        'BEL'  => 'Белово',
        'ACH'  => 'Ачинск',
        'NVKZ' => 'Новокузнецк',
        'KRSK' => 'Красноярск',
    ];

    private $timezone = [
        'NSK'  => 'Asia/Novosibirsk',
        'BEL'  => 'Asia/Krasnoyarsk',
        'ACH'  => 'Asia/Krasnoyarsk',
        'NVKZ' => 'Asia/Krasnoyarsk',
        'KRSK' => 'Asia/Krasnoyarsk',
    ];
    private $baseUrl = 'http://dzzzr.ru';

    public function startParse()
    {
        $return = [];
        foreach ($this->urls as $city => $url) {
            try {
                $quests = $this->getQuests($url, $city);
                $return = array_merge($return, $quests);

            } catch (\Exception $e) {
                print("skip $city [$url] {$e->getMessage()}" . PHP_EOL);
            }
        }

        return $return;
    }

    private function getQuests($url, $city)
    {
        $page    = $this->get($url);
        $pattern = '/<a name=gm([0-9]+)>.*?Date><a name=([0-9\s:\-]+).*?<h2 class=gameTitle>(.*?)<\/h2>.*?<div id=anons(.*?)GAMEID/isu';
        $quests  = [];
        if (preg_match_all($pattern, $page, $questsRaw)) {
            /** @var Collection $collection */
            $collection = collect(array_get($questsRaw, 0, []));
            foreach ($collection->keys()->all() as $key) {
                $tz    = new \DateTimeZone(array_get($this->timezone, $city, 'Asia/Novosibirsk'));
                $start = Carbon::createFromFormat('Y-m-d H:i', $questsRaw[2][$key], $tz);
                $stop  = Carbon::createFromFormat('Y-m-d H:i', $questsRaw[2][$key], $tz)->addHours(5);

                $quest = new QuestData();
                $quest
                    ->setDescription(html_entity_decode($this->getInfo($questsRaw[4][$key])))
                    ->setGameId($questsRaw[1][$key])
                    ->setKey('DOZOR', $city, $quest->getGameId())
                    ->setLink($url)
                    ->setPlacement(array_get($this->placements, $city))
                    ->setStart($start)
                    ->setStop($stop)
                    ->setTitle(html_entity_decode($questsRaw[3][$key]), $city);
                $quests[] = $quest;
            }
        }

        return $quests;
    }

    public function get($url)
    {
        return iconv('cp1251', 'utf8', (string) $this->client->get($url)->getBody());
    }

    /**
     * @param string $data
     */
    private function getInfo($data)
    {
        if (!preg_match_all('/<strong id=orang>(.*?)<\/strong>(.*?)<\/td>/', $data, $lines)) {
            return null;
        }

        $info = '';

        foreach (collect($lines[1])->keys()->all() as $key) {
            $line = trim(preg_replace('/<br>/', PHP_EOL, $lines[2][$key]));
            $info .= $lines[1][$key] . ' ' . strip_tags($line) . PHP_EOL;
        }


        return trim($info);
    }

    function init()
    {
        return $this;
    }

    protected function getKey($city, $gameId)
    {
        return sprintf('%s:%s:%s', 'Dozor', $city, $gameId);
    }
}