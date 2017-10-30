<?php

namespace App\Http\Controllers;

use App\City;
use App\Game;
use App\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

    public function games(Request $request)
    {
        $search = $request->get('q');
        $query = Game::limit(10)->with(['city', 'city.project']);
        if ($search) {
            $query->where('title', 'ILIKE', '%'. $search.'%');
            $query->orWhereHas('city', function ($query) use ($search) {
                $query->where('title',  'ILIKE', '%'. $search.'%');
                $query->orWhere('url',  'ILIKE', '%'. $search.'%');
            });
        }
        return response()->json($query->get());
    }

    public function create()
    {
        return null;
    }
}
