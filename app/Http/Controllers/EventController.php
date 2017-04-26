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

        $game = ($request->has('game')) ? $request->get('game') : null;
        $orders = Order::where('event_id', $id)->get();

        $teams = $orders->filter(function($value) use ($game) {
            return (!is_null($game)) ? $value->products()->where('product_id', $game)->count() > 0 : true;
        })->map(function($order){
            return $order->team();
        })->filter(function($value) {
            return !is_null($value);
        })->unique()->values();

        return ($teams->count()>0) ? response()->json($teams) : response()->json(['error' => 'No team found'], 404);

//        $participations = null;
//
//        if($request->has('game'))
//            $participations = Event::find($id)->participations()->where('product_id', $request->get('game'))->get();
//        else
//            $participations = Event::find($id)->participations()->get();
//
//        $teams = collect();
//        foreach ($participations as $participation){
//            $teams->push($participation->team->get()[0]);
//
//        }
//
//        return response()->json($teams->unique());
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
