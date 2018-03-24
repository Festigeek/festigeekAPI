<?php

namespace App\Http\Controllers;

use App\Event;
use App\Order;
use App\Team;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Get subcribed teams
     *
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

    public function updateTeam(Request $request, $event_id, $team_id) {
        if(!$request->has('users') || !$request->has('captain'))
            return response()->json(['error' => 'Missing parameters.'], 422);

        $event = Event::find($event_id)->first();
        $team = Team::find($team_id)->first();
        $users_ids = collect($request->get('users'));
        $nb_users = $users_ids->count();
        $captain_id = $request->get('captain');

        if(is_null($event))
            return response()->json(['error' => 'Event not found.'], 404);

        if(is_null($team))
            return response()->json(['error' => 'Team not found.'], 404);

        // Can only REMOVE users.
        if(!$team->hasUser($captain_id) || $team->users()->get()->count() < $nb_users || $nb_users === 0)
            return response()->json(['error' => 'Request was well-formed but was unable to be followed due to content errors'], 422);

        if(!$this->isAdminOrOwner($team->captain->id)) {
            return response()->json(['error' => 'Invalid Credentials.'], 401);
        }

        // If only one user in team, automatically promoted as captain
        if($nb_users === 1)
            $captain_id = $users_ids->first();

        // Can only REMOVE users. Adding users would result with no related order intermediate table
        $users = $users_ids->mapWithKeys(function($item) use($team, $captain_id) {
            $order_id = $team->users()->where('user_id', '=', $item)->first()->pivot->orderId;
            return [$item => [
                'captain' => ($item === $captain_id) ? 1 : 0,
                'order_id' => $team->orders()->where('orders.user_id', '=', $item)->first()->id
            ]];
        });

        $team->users()->detach();
        $team->users()->attach($users->all());
        $team->save();

        return response()->json(['success' => 'Team updated'], 200);
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
