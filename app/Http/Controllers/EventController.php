<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{

    /**
     * @param Request $request
     * @param $id event id
     * @param $game id product
     */
    public function teams(Request $request, $id, $game = null){

        $participations = null;
        if($game === null)
            $participations = Event::find($id)->participations()->get();
        else
            $participations = Event::find($id)->participations()->where('product_id', $game)->get();

        $teams = collect();
        foreach ($participations as $participation){
            $teams->push($participation->team->get()[0]);

        }

        return response()->json($teams->unique());
    }
}
