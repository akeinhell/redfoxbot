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
        foreach ($chats as $chatId) {
            try {
                $this->info('scan ' . $chatId);
                $engine = $this->getEngine($chatId);
                $actualLevelList = collect($engine->getQuestList());
                 /* $actualLevelList = collect([
                    1 => 'test 1',
                    2 => 'test2',
                    3 => 'test3',
                    4 => 'test4',
                    5 => 'test5',
                ]); */
                $oldLevels       = collect(\Cache::get('TRACK:' . $chatId, [
//                    5 => 'test5',
                ]));

                $diffKeys = $actualLevelList->diffKeys($oldLevels);
                if ($diffKeys->count()) {
                    Bot::action()->sendMessage($chatId, $this->formatMessage($diffKeys));
                    $self = &$this;
                    if ($engine instanceof CanTrackingInterface) {
                        $diffKeys->each(function ($title, $id) use ($engine, $self) {
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
