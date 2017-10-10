<?php

namespace App\Http\Controllers;


use App\City;
use App\Game;
use App\Project;
use Carbon\Carbon;

class ApiController extends Controller
{
    public function projects() {
        return response()->json(Project::all());
    }

    public function cities() {
        return response()->json(City::with('project')->get());
    }

    public function games() {
        return response()->json(Game::with(['city', 'city.project'])->get());
    }

    public function create(){
        $game = new Game([
            'title' => 'test_title',
            'description' => 'description',
            'start_time' => Carbon::now()->toAtomString(),

        ]);
        $project = Project::create([
            'title' => 'test_title',
            'url' => 'test_url',
            'about' => 'about',
            'config' => '{}',
        ]);

        $city = City::create([
            'title' => 'Новосиб',
            'url' => 'url',
            'project_id' => $project->id,
        ]);
        $city->project()->associate($project);
        $game->city()->associate($city);
        $game->save();
        return response()->json($game);
    }
}
