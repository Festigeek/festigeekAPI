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
    public function teams(Request $request, $id){
        $participations = null;

        if($request->has('game'))
            $participations = Event::find($id)->participations()->where('product_id', $request->get('game'))->get();
        else
            $participations = Event::find($id)->participations()->get();

        $teams = collect();
        foreach ($participations as $participation){
            $teams->push($participation->team->get()[0]);

        }

        return response()->json($teams->unique());
    }

    /**
     * @param Request $request
     * @param $id event id
     */
    public function products(Request $request, $id){
        $products = Event::find($id)->products()->get();

        return response()->json($products);
    }
}
