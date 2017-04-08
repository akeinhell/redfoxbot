<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 28.09.16
 * Time: 17:08.
 */

namespace App\Quests;

use Symfony\Component\DomCrawler\Crawler;

class DozorClassicQuest extends BaseQuest
{
    /**
     * DozorClassicQuest constructor.
     *
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->html    = $crawler->html();
        $this->crawler = $crawler;
        $this->parse();
    }

    public function isAuth()
    {
        // TODO: Implement isAuth() method.
    }

    public function isRunning()
    {
        // TODO: Implement isRunning() method.
    }

    public function getText()
    {
        // TODO: Implement getText() method.
    }

    public function getHints()
    {
        // TODO: Implement getHints() method.
    }

    public function getHint($id)
    {
        // TODO: Implement getHint() method.
    }

    public function getImages()
    {
        // TODO: Implement getImages() method.
    }

    public function getCoordinates()
    {
        // TODO: Implement getCoordinates() method.
    }

    public function getTime()
    {
        $pattern = '/countDown\(([0-9])\)\'/isu';
        if (preg_match($pattern, $this->html, $match)) {
            $seconds = (int) $match[1];

            return 'Время до слива: ' . gmdate('H:i:s', $seconds);
        }

        return $pattern;
    }

    public function getSpoiler()
    {
        // TODO: Implement getSpoiler() method.
    }

    public function getTitle()
    {
        // TODO: Implement getTitle() method.
    }

    public function getId()
    {
        // TODO: Implement getId() method.
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getQuests()
    {
        return collect($this->crawler->filter('div.zad')->each(function(Crawler $level) {
            $html = trim($level->html());

            return preg_replace('/(<br>)/', PHP_EOL, $html);
        }));
    }

    public function getBonuses()
    {
        // TODO: Implement getBonuses() method.
    }

    public function getActiveBonuses()
    {
        // TODO: Implement getActiveBonuses() method.
    }

    public function getEstimatedCodes()
    {
        $level = $this->getQuests()->first();
        if ($level) {
            $est = collect($this->crawler->filter('div.title div')->each(function(Crawler $div) {
                return sprintf('<b>%s</b>', $div->text());
            }));

            $codesHtml = strip_tags(preg_replace('/(<p>.*?<\/p>)/isu', '', $level), '<p><strong><br><span>');
            $codesHtml = preg_replace('/(<br>)/', PHP_EOL, $codesHtml);
            $codesHtml = preg_replace('/(<\/p>)/', '', $codesHtml);
            $codesHtml = preg_replace('/([\r\n])/', '', $codesHtml);
//            $codesHtml = trim(preg_replace('/([\s]{2,})/', PHP_EOL, $codesHtml));

            $codesPart = collect(explode('</strong>', $codesHtml));
            $return    = strip_tags($codesPart->shift()) . PHP_EOL;
            $codesPart = $codesPart
                ->map(function($line) {
                    $parts  = explode(':', $line, 3);
                    $codes  = explode(',', array_pop($parts));
                    $prefix = implode(':', $parts) . ': ';

                    $codes = array_filter($codes, function($code) {
                        return trim(strip_tags($code)) === trim($code);
                    });

                    return [
                        'codes'  => $codes,
                        'prefix' => strip_tags($prefix),
                    ];
                })
                ->map(function($item) {
                    return array_get($item, 'prefix', '') . strip_tags(implode(',', array_get($item, 'codes', [])));
                });

            $ret = ($est->count() ? $est->first() : '') . PHP_EOL . implode(PHP_EOL, $codesPart->toArray());

            return $ret;
        }

        return '';
    }

    public function getCrawler()
    {
        return $this->crawler;
    }

    public function getSystemMessage()
    {
        $msg = $this->crawler->filter('div.sysmsg b');

        return $msg->count() ? $msg->first()->text() : '';
    }

    private function parse()
    {
    }
}
