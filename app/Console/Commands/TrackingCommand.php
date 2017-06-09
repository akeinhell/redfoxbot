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
	dump($chats);
//        $chats = [94986676];
        foreach ($chats as $chatId) {
            try {
                $cacheKey = 'TRACK:' . $chatId;
                $this->info('scan ' . $cacheKey);
                $engine = $this->getEngine($chatId);
                $actualLevelList = $engine->getQuestList();
//                 $actualLevelList = collect([
//                    1 => 'test 1',
//                    2 => 'test2',
//                    3 => 'test3',
//                    4 => 'test4',
//                    5 => 'test5',
//                ]);
//		dump('actual', $actualLevelList);
                $oldLevels       = \Cache::get($cacheKey, []);
//		dump('old', $oldLevels);

                $diffKeys = array_flip(array_diff($actualLevelList, $oldLevels));
//                dump('diff ', $diffKeys);
                if ($diffKeys) {
                    $newSet = array_flip(array_merge($oldLevels, $diffKeys));
//		    dump('newSet', $newSet);
                    \Cache::put($cacheKey, $newSet, 60 * 24 * 7);
                    Bot::action()->sendMessage($chatId, $this->formatMessage($diffKeys));
                    
                    if ($engine instanceof CanTrackingInterface) {
//                   	dump('fetch', $newSet);                
                  	foreach($newSet as $id => $title) {
			    $this->info('fetch:    '.$id. ':  '.$title);
			} 
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

    private function formatMessage(array $diffKeys)
    {
        return '#Вброс' . PHP_EOL . 'Новые задания: ' . PHP_EOL . implode(PHP_EOL, array_flip($diffKeys));
    }
}
