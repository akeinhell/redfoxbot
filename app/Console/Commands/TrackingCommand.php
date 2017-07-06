<?php

namespace App\Console\Commands;

use App\Games\BaseEngine\AbstractGameEngine;
use App\Games\Interfaces\CanTrackingInterface;
use App\Telegram\Bot;
use App\Telegram\Config;
use Illuminate\Console\Command;

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
        dump($chats);
        foreach ($chats as $chatId) {
            try {
                $cacheKey = 'TRACK:' . $chatId;
                $this->info('scan ' . $cacheKey);
                $engine          = $this->getEngine($chatId);
                $actualLevelList = $engine->getQuestList();

                $oldLevels = \Cache::get($cacheKey, []);

                $diffKeys = array_diff($actualLevelList, $oldLevels);
                if ($diffKeys) {
                    $newSet = $oldLevels + $diffKeys;
                    \Cache::put($cacheKey, $newSet, 60 * 24 * 7);
                    Bot::action()->sendMessage($chatId, $this->formatMessage($diffKeys));

                    if ($engine instanceof CanTrackingInterface) {
                        foreach ($newSet as $id => $title) {
                            $this->info('fetch:    ' . $id . ':  ' . $title);
                            try {
                                if ($chatId == 94986676) {
                                    $html = $engine->getRawHtml($id);
                                    $dir  = '/var/www/safari/dump';
                                    @\File::makeDirectory($dir, 0777, true);
                                    file_put_contents($dir . '/' . $title . '.html', $html);
                                }
                            }catch (\Exception $e){
                                $this->warn($e->getMessage());
                            }
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
        return '#Вброс' . PHP_EOL . 'Новые задания: ' . PHP_EOL . implode(PHP_EOL, $diffKeys);
    }
}
