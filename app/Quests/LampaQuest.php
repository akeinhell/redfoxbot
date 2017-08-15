<?php
/**
 * Created by PhpStorm.
 * User: akeinhell
 * Date: 08.07.16
 * Time: 14:13.
 */

namespace App\Engines;

use App\Exceptions\TelegramCommandException;
use App\Quests\BaseQuest;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\DomCrawler\Crawler;

class LampaQuest extends BaseQuest
{
    /**
     * @var Collection
     */
    protected $quests;

    public function __construct($html)
    {
        parent::__construct($html);
        $this->parse();
    }

    private function parse()
    {
        $questNodes = new Collection($this->crawler
            ->filter('h3')
            ->each(function (Crawler $node) {
                $div = $node->filter('div')->getNode(0) ?: $node->filter('span')->getNode(0);
                if (!$div) {
                    return null;
                }
                $id  = $div->getAttribute('id');
                $ids = explode('_', $id);
                $id  = count($ids) > 1 ? last($ids) : last(explode('-', $id));

                return [
                    'title' => $node->text(),
                    'id'    => $id,
                ];
            }));
        $crawler    = &$this->crawler;

        $this->quests = $questNodes
            ->filter(function ($i) {
                return $i;
            })
            ->map(function ($quest, $id) use ($crawler) {
                $items = $crawler->filter('#levels-accord > div');

                $q             = $items->eq($id);
                $quest['text'] = $q->filter('.level-quest')->first()->html();
                $coords        = $q->filter('.coords');
                if ($coords->count()) {
                    $quest['coords'] = $coords->first()->text();
                }

                $codes          = $q->filter('.items span');
                $codeCollection = new Collection($codes->each(function (Crawler $node) {
                    return [
                        'text'     => $node->text(),
                        'accepted' => $node->attr('class') === 'accepted',
                        'id'       => $node->attr('id'),
                    ];
                }));

                $quest['codes'] = $codeCollection
                    ->filter(function ($item) {
                        return array_get($item, 'id') && array_get($item, 'text');
                    })
                    ->map(function ($code) {
                        list($metka, $params) = explode('(', array_get($code, 'text'));
                        $params = array_map(function ($item) {
                            return last(explode(':', $item)); //;last(explode());
                        }, explode(',', trim($params, '()')));

                        return array_merge($code, [
                            'ko'    => array_get($params, 0),
                            'code'  => array_get($params, 1),
                            'metka' => $metka,
                        ]);
                    })
                    ->groupBy('ko');

                $quest['estCodes'] = $quest['codes']
                    ->filter(function ($item) {
                        return !array_get($item, 'accepted');
                    })
                    ->map(function ($item, $key) {
                        return $key . (count($item) > 1 ? sprintf(' (%s шт)', count($item)) : '');
                    })
                    ->toArray();

                return $quest;
            });
    }

    public function isGameSelected()
    {
        return !preg_match('/неактуальные куки/isu', $this->html);
    }

    public function isAuth()
    {
        return !preg_match('/LoginForm_username/isu', $this->html);
    }

    public function isRunning()
    {
        // TODO: Implement isRunning() method.
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
        $html = $this->getText();
        if (preg_match_all('/<img.*?src="(.*?)"/isu', $html, $matches)) {
            return $matches[1];
        }

        return [];
    }

    public function getText()
    {
        $level = $this->quests->first();
        if (!($text = array_get($level, 'text'))) {
            throw new TelegramCommandException('Не удалось получить текст задания');
        }

        return $text;
    }

    public function getCoordinates()
    {
        // TODO: Implement getCoordinates() method.
    }

    public function getTime()
    {
        // TODO: Implement getTime() method.
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
     * @throws \Exception
     *
     * @return Collection
     */
    public function getQuests()
    {
        $quests = $this->quests;
        if (!$quests->count()) {
            throw  new TelegramCommandException('Не возможно получить список заданий');
        }

        return $quests;
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
        // TODO: Implement getEstimatedCodes() method.
        return [];
    }

    public function getQuestById($id)
    {
        return $this->quests->get($id) ?: $this->quests->first();
    }
}
