<?php

namespace App\Http\Controllers;

use App\City;
use App\Game;
use App\Project;
use Carbon\Carbon;

class ApiController extends Controller
{
    public function projects()
    {
        return response()->json(Project::all());
    }

    public function cities()
    {
        return response()->json(City::with('project')->get());
    }

    public function games()
    {
        return response()->json(Game::with(['city', 'city.project'])->get());
    }

    public function create()
    {
        return null;
    }
}
