<?php

namespace App\Http\Controllers;

use App\Event;
use App\Order;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * @param Request $request
     * @param String $id
     * @param mixed $game (product_id)
     */
    public function teams(Request $request, $id){

        $game = ($request->filled('game')) ? $request->get('game') : null;
        $orders = (!is_null($game)) ? Order::where('event_id', $id)->has('products', $game)->get() : Order::where('event_id', $id)->get();

        try{
            $teams = Event::findOrFail($id)->teams();
        }
        catch (\Exception $e) {
            return response()->json(['error' => 'Event not found.'], 404);
        }

        $filteredTeams = $teams->filter(function($team) use($orders) {
            return $orders->pluck('team')->contains('id', $team->id);
        });

        return response()->json($filteredTeams);

//        $temp = $orders->filter(function($value) use ($game) {
//            return (!is_null($game)) ? $value->products()->where('product_id', $game)->count() > 0 : true;
//        })->map(function($order) {
//            return $order->team()->get();
//        })->flatten()->filter(function($value) {
//            return !is_null($value);
//        })->unique('id')->sortBy('name')->values();
//
//        return response()->json($temp);
    }

    /**
     * @param Request $request
     * @param $id event id
     */
    public function products(Request $request, $id){
        $products = Event::find($id)->products()->get();
        return response()->json($products);
    }

    /**
     * @param Request $request
     * @param $id event id
     */
    public function current(){
        $currentEvent = Event::whereDate('ends_at', '>=', date('Y-m-d'))->orderBy('begins_at', 'asc')->first();
        return response()->json($currentEvent);
    }
}
