<?php

namespace App\Console\Commands;

use App\Games\BaseEngine\AbstractGameEngine;
use App\Games\Interfaces\CanTrackingInterface;
use App\Telegram\Bot;
use App\Telegram\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class TrackingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'tracking:run';

    private function getEngine($chatId): AbstractGameEngine
    {
        $config = Config::get($chatId);
        if (!$config || !$config->project) {
            throw new \Exception('Setting is not available');
        }
        $projectClass = '\\App\\Games\\Engines\\' . $config->project . 'Engine';

        /* @var AbstractGameEngine $engine */
        return new $projectClass($chatId);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $chats = \Track::getChatList();
//        $chats = [94986676];
        foreach ($chats as $chatId) {
            try {
                $cacheKey = 'TRACK:' . $chatId;
                $this->info('scan ' . $cacheKey);
                $engine = $this->getEngine($chatId);
                $actualLevelList = collect($engine->getQuestList());
//                 $actualLevelList = collect([
//                    1 => 'test 1',
//                    2 => 'test2',
//                    3 => 'test3',
//                    4 => 'test4',
//                    5 => 'test5',
//                ]);
                $oldLevels       = collect(\Cache::get($cacheKey, []));
                $this->info('$oldLevels '. $oldLevels->toJson());

                $diffKeys = $actualLevelList->diffKeys($oldLevels);
                $this->info('diff '. $diffKeys->toJson());
                if ($diffKeys->count()) {
                    $newSet = $oldLevels->merge($diffKeys->toArray());
                    $this->info('new set '. $newSet->toJson());
                    \Cache::put($cacheKey, $newSet->toArray(), 60 * 24 * 7);
                    Bot::action()->sendMessage($chatId, $this->formatMessage($diffKeys));
                    $self = &$this;
                    if ($engine instanceof CanTrackingInterface) {
                        $diffKeys->each(function ($title, $id) use ($engine, $self, &$oldLevels) {
                            $oldLevels->put($id, $title);
                            $self->info('get info from ' . $title . ':' . $id);
                            /** @var $engine CanTrackingInterface */
//                            $engine->getRawHtml($id);
                        });
                    }
                }
            } catch (\Exception $e) {
                $this->warn('remove chat: ' . $chatId . ': ' . $e->getMessage());
                try {
                    \Track::removeChat($chatId, $e->getMessage());
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        }
    }

    private function formatMessage(Collection $diffKeys)
    {
        return '#Вброс' . PHP_EOL . 'Новые задания: ' . PHP_EOL . $diffKeys->implode(PHP_EOL);
    }
}
