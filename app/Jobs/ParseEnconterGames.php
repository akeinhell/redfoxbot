<?php

namespace App\Jobs;

use App\City;
use App\Game;
use App\Jobs\Job;
use App\Project;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ParseEnconterGames extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    protected $page;
    protected $params;
    protected $demo;

    /**
     * Create a new job instance.
     *
     * @param int $page
     * @param array $params
     * @param bool $demoSite
     */
    public function __construct($page = 1, $params = [], $demoSite = false)
    {
        $this->page = $page;
        $this->params = $params;
        $this->demo = $demoSite;
    }

    /**
     * Execute the job.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->params) {
            foreach (\Encounter::getPermutations() as $params) {
                dispatch(new ParseEnconterGames(1, $params, $this->demo));
            }
            return;
        }
        \Log::info('parse en', array_merge(['page' => $this->page], $this->params));
        $result = \Encounter::getGames($this->params, $this->page, $this->demo);

        $lastPage = array_get($result, 'lastPage');

        if ($lastPage > 1 && $this->page == 1) {
            foreach (range($this->page + 1, $lastPage) as $page) {
                dispatch(new ParseEnconterGames($page, $this->params, $this->demo));
            }
        }

        $games = array_get($result, 'games', []);
        if (!$games) {
            return;
        }
        foreach ($games as $game) {
            $project = Project::firstOrCreate([
                'title' => 'Encounter',
                'about' => '',
                'config' => '{}',
                'url' => 'http://en.cx/'
            ]);

            $domain = array_get($game, 'domain');
            $city = City::firstOrCreate([
                'title' => $domain,
                'url' => $domain,
                'project_id' => $project->id
            ]);

            $gameModel = Game::firstOrNew([
                'gid' => array_get($game, 'id'),
                'city_id' => $city->id
            ]);
            $gameModel->fill([
                'title' => array_get($game, 'title'),
                'start' => array_get($game, 'start'),
            ]);
            $gameModel->save();
        }

        sleep(1);
    }
}
